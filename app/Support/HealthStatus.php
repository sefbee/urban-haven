<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class HealthStatus
{
    /**
     * @return array<string, array{ok: bool, critical: bool, detail: string}>
     */
    public static function run(): array
    {
        return [
            'database' => self::check(true, function (): string {
                DB::select('select 1');

                return 'Connected';
            }),
            'cache' => self::check(true, function (): string {
                $key = 'uh-health:'.Str::random(8);
                Cache::put($key, 'ok', 10);
                $ok = Cache::get($key) === 'ok';
                Cache::forget($key);

                return $ok ? 'Read/write OK' : throw new \RuntimeException('Cache read-back failed');
            }),
            'storage' => self::check(true, function (): string {
                $path = 'health/'.Str::random(8).'.txt';
                Storage::disk('local')->put($path, 'ok');
                Storage::disk('local')->delete($path);

                return 'Writable';
            }),
            'queue' => self::check(false, function (): string {
                $failed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;
                $pending = Schema::hasTable('jobs') ? DB::table('jobs')->where('created_at', '<', now()->subMinutes(15)->timestamp)->count() : 0;

                if ($failed > 0 || $pending > 0) {
                    throw new \RuntimeException("{$failed} failed jobs, {$pending} jobs waiting over 15 minutes");
                }

                return 'No failed or stuck jobs';
            }),
            'mail' => self::check(false, function (): string {
                if (! self::mailConfigured()) {
                    throw new \RuntimeException('Mail driver is '.config('mail.default').'; notifications are not delivered');
                }

                return 'Mailer '.config('mail.default');
            }),
        ];
    }

    public static function mailConfigured(): bool
    {
        return ! in_array(config('mail.default'), ['log', 'array', null], true);
    }

    /**
     * @param  callable(): string  $probe
     * @return array{ok: bool, critical: bool, detail: string}
     */
    private static function check(bool $critical, callable $probe): array
    {
        try {
            return ['ok' => true, 'critical' => $critical, 'detail' => $probe()];
        } catch (Throwable $exception) {
            return ['ok' => false, 'critical' => $critical, 'detail' => mb_strimwidth($exception->getMessage(), 0, 160, '…')];
        }
    }
}
