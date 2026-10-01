<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;

class StaleRecordException extends ValidationException
{
    /**
     * @param  array<string, mixed>  $current
     */
    public static function withCurrent(array $current): self
    {
        $summary = collect($current)
            ->map(fn (mixed $value, string $key): string => str_replace('_', ' ', $key).': '.(is_scalar($value) && $value !== '' ? $value : '—'))
            ->implode(', ');

        /** @var self $exception */
        $exception = self::withMessages([
            'version' => 'Someone else changed this record. Latest values — '.$summary.'. Review them and save again.',
        ]);

        return $exception;
    }
}
