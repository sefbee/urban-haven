<?php

namespace App\Support;

final class PhoneNumber
{
    private const BANGLADESH_MOBILE = '/^(?:\+?880|0)?(1[3-9]\d{8})$/';

    private const INTERNATIONAL = '/^\+[1-9]\d{7,14}$/';

    /**
     * Bangladesh mobiles become +8801XXXXXXXXX; other numbers must already be in international form.
     */
    public static function normalize(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $compact = preg_replace('/[\s\-().]/', '', trim($input)) ?? '';

        if (str_starts_with($compact, '00')) {
            $compact = '+'.substr($compact, 2);
        }

        if (preg_match(self::BANGLADESH_MOBILE, $compact, $matches) === 1) {
            return '+880'.$matches[1];
        }

        if (preg_match(self::INTERNATIONAL, $compact) === 1 && ! str_starts_with($compact, '+880')) {
            return $compact;
        }

        return null;
    }

    public static function isValid(?string $input): bool
    {
        return self::normalize($input) !== null;
    }

    public static function telHref(?string $input): ?string
    {
        $normalized = self::normalize($input) ?? (filled($input) ? preg_replace('/[^\d+]/', '', (string) $input) : null);

        return $normalized ? 'tel:'.$normalized : null;
    }

    public static function whatsappHref(?string $input, ?string $message = null): ?string
    {
        $normalized = self::normalize($input);

        if (! $normalized) {
            return null;
        }

        $href = 'https://wa.me/'.ltrim($normalized, '+');

        return filled($message) ? $href.'?text='.rawurlencode($message) : $href;
    }
}
