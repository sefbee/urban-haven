<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class DisplayTimezone
{
    public static function name(): string
    {
        return (string) config('urbanhaven.display_timezone', 'Asia/Dhaka');
    }

    public static function format(?CarbonInterface $timestamp, string $format = 'd M Y, h:i A'): ?string
    {
        if ($timestamp === null) {
            return null;
        }

        return Carbon::instance($timestamp)
            ->timezone(self::name())
            ->format($format);
    }
}
