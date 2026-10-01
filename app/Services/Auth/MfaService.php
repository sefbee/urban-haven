<?php

namespace App\Services\Auth;

use App\Contracts\AuditLogger;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class MfaService
{
    public const RECOVERY_CODE_COUNT = 8;

    public function __construct(
        private readonly Google2FA $google2fa,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    public function qrCodeSvg(User $user, string $secret): string
    {
        $uri = $this->google2fa->getQRCodeUrl((string) config('urbanhaven.mfa.issuer'), $user->email, $secret);

        return (new Writer(new ImageRenderer(new RendererStyle(200, 1), new SvgImageBackEnd)))->writeString($uri);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        return preg_match('/^\d{6}$/', $code) === 1 && $this->google2fa->verifyKey($secret, $code, 1);
    }

    /**
     * @return list<string> Plain recovery codes, shown once.
     */
    public function enable(User $user, string $secret): array
    {
        $codes = $this->freshRecoveryCodes();

        $user->forceFill([
            'mfa_secret' => $secret,
            'mfa_recovery_codes' => array_map(fn (string $code): string => hash('sha256', $code), $codes),
            'mfa_enabled_at' => now(),
        ])->save();

        $this->auditLogger->record($user->id, 'auth.mfa_enabled', User::class, $user->id, null, null, request()->ip());

        return $codes;
    }

    /**
     * Accepts a current TOTP code or consumes one recovery code.
     */
    public function attempt(User $user, string $input): bool
    {
        if (! $user->hasMfaEnabled()) {
            return false;
        }

        if ($this->verifyCode((string) $user->mfa_secret, $input)) {
            return true;
        }

        $hash = hash('sha256', strtoupper(trim($input)));
        $stored = (array) ($user->mfa_recovery_codes ?? []);

        if (! in_array($hash, $stored, true)) {
            return false;
        }

        $user->forceFill(['mfa_recovery_codes' => array_values(array_diff($stored, [$hash]))])->save();
        $this->auditLogger->record($user->id, 'auth.mfa_recovery_code_used', User::class, $user->id, null, ['remaining' => count($stored) - 1], request()->ip());

        return true;
    }

    public function reset(User $user, User $actor): void
    {
        $user->forceFill([
            'mfa_secret' => null,
            'mfa_recovery_codes' => null,
            'mfa_enabled_at' => null,
        ])->save();

        $this->auditLogger->record($actor->id, 'staff.mfa_reset', User::class, $user->id, null, null, request()->ip());
    }

    /**
     * @return list<string>
     */
    private function freshRecoveryCodes(): array
    {
        return array_map(
            fn (): string => strtoupper(Str::random(5).'-'.Str::random(5)),
            range(1, self::RECOVERY_CODE_COUNT),
        );
    }
}
