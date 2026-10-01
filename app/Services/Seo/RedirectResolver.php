<?php

namespace App\Services\Seo;

use App\Models\Redirect;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RedirectResolver
{
    private const MAX_HOPS = 5;

    /**
     * Follows a chain to its final target in one response so visitors and crawlers never hop twice.
     *
     * @return array{to: string, code: int}|null
     */
    public function resolve(string $path): ?array
    {
        $current = Redirect::normalizePath($path);
        $seen = [$current => true];
        $first = null;
        $code = 301;

        for ($hop = 0; $hop < self::MAX_HOPS; $hop++) {
            $redirect = Redirect::query()->active()->where('from_path', $current)->first();

            if ($redirect === null) {
                break;
            }

            $first ??= $redirect;
            $code = $hop === 0 ? $redirect->http_code : $code;
            $target = $redirect->to_path;

            if (preg_match('#^https?://#i', $target) === 1) {
                $current = $target;
                break;
            }

            $current = Redirect::normalizePath($target);

            if (isset($seen[$current])) {
                return null;
            }

            $seen[$current] = true;
        }

        if ($first === null) {
            return null;
        }

        DB::table('redirects')->where('id', $first->id)->update([
            'hits' => DB::raw('hits + 1'),
            'last_hit_at' => now(),
        ]);

        return ['to' => $current, 'code' => in_array($code, [301, 302, 307, 308], true) ? $code : 301];
    }

    /**
     * Creates or updates a redirect after rejecting loops and pointing existing chains at the new target.
     */
    public function store(string $from, string $to, int $code = 301, ?string $reason = null, ?User $actor = null): Redirect
    {
        $from = Redirect::normalizePath($from);
        $isExternal = preg_match('#^https?://#i', $to) === 1;
        $to = $isExternal ? $to : Redirect::normalizePath($to);

        if ($from === $to) {
            throw ValidationException::withMessages(['to_path' => 'A redirect cannot point to itself.']);
        }

        if (str_starts_with($from, '/admin')) {
            throw ValidationException::withMessages(['from_path' => 'Admin paths cannot be redirected.']);
        }

        if (! $isExternal && $this->leadsBackTo($to, $from)) {
            throw ValidationException::withMessages(['to_path' => 'That redirect would create a loop.']);
        }

        if (! $isExternal) {
            $to = $this->finalTarget($to);
        }

        return DB::transaction(function () use ($from, $to, $code, $reason, $actor): Redirect {
            Redirect::query()->where('to_path', $from)->update(['to_path' => $to]);

            return Redirect::query()->updateOrCreate(
                ['from_path' => $from],
                [
                    'to_path' => $to,
                    'http_code' => $code,
                    'reason' => $reason,
                    'created_by' => $actor?->id,
                    'is_active' => true,
                    'created_at' => now(),
                ],
            );
        });
    }

    private function finalTarget(string $path): string
    {
        $current = $path;

        for ($hop = 0; $hop < self::MAX_HOPS; $hop++) {
            $next = Redirect::query()->active()->where('from_path', $current)->value('to_path');

            if ($next === null) {
                break;
            }

            $current = preg_match('#^https?://#i', $next) === 1 ? $next : Redirect::normalizePath($next);
        }

        return $current;
    }

    private function leadsBackTo(string $start, string $origin): bool
    {
        $current = $start;

        for ($hop = 0; $hop < self::MAX_HOPS * 2; $hop++) {
            $next = Redirect::query()->active()->where('from_path', $current)->value('to_path');

            if ($next === null) {
                return false;
            }

            $next = Redirect::normalizePath($next);

            if ($next === $origin) {
                return true;
            }

            $current = $next;
        }

        return true;
    }
}
