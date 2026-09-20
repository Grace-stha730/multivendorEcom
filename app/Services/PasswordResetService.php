<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\ShopUser;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Emailed 6-digit code password reset for every account type.
 * One code per (guard, identifier). Codes are hashed, expire, and lock after too many wrong tries.
 */
class PasswordResetService
{
    public const TTL_MINUTES = 10;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;

    /** guard => [model, identifier column, label] */
    public const GUARDS = [
        'web' => [User::class, 'email', 'Email'],
        'admin' => [Admin::class, 'email', 'Email'],
        'shop_user' => [ShopUser::class, 'username', 'Username'],
    ];

    public static function label(string $guard): string
    {
        return self::GUARDS[$guard][2];
    }

    /**
     * Returns false only when the code could not be sent (or the cooldown applies).
     * Unknown accounts return true so callers can't tell whether an account exists.
     */
    public function sendCode(string $guard, string $identifier): bool
    {
        $identifier = $this->normalize($guard, $identifier);
        $account = $this->find($guard, $identifier);

        if (!$account) {
            return true;
        }

        $existing = DB::table('password_reset_codes')->where(['guard' => $guard, 'identifier' => $identifier])->first();
        if ($existing && now()->diffInSeconds($existing->sent_at, true) < self::RESEND_COOLDOWN_SECONDS) {
            return true; // silently ignore rapid re-requests; the previous code is still valid
        }

        $email = $this->emailFor($guard, $account);
        if (!$email) {
            return true;
        }

        $code = (string) random_int(100000, 999999);

        try {
            // Send first so a mail failure never leaves an unusable code behind.
            Mail::mailer('smtp')->raw(
                "Your password reset code is: {$code}\n\nIt expires in " . self::TTL_MINUTES . " minutes. If you didn't request this, ignore this email.",
                fn ($m) => $m->to($email)->subject('Reset your password')
            );
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        DB::table('password_reset_codes')->updateOrInsert(
            ['guard' => $guard, 'identifier' => $identifier],
            [
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(self::TTL_MINUTES),
                'sent_at' => now(),
                'attempts' => 0,
                'updated_at' => now(),
                'created_at' => $existing->created_at ?? now(),
            ]
        );

        return true;
    }

    /** True if the code was valid and the password was changed. */
    public function reset(string $guard, string $identifier, string $code, string $newPassword): bool
    {
        $identifier = $this->normalize($guard, $identifier);
        $row = DB::table('password_reset_codes')->where(['guard' => $guard, 'identifier' => $identifier])->first();
        $account = $this->find($guard, $identifier);

        if (!$row || !$account || now()->greaterThan($row->expires_at) || $row->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (!Hash::check($code, $row->code_hash)) {
            DB::table('password_reset_codes')->where('id', $row->id)->increment('attempts');

            return false;
        }

        DB::transaction(function () use ($account, $newPassword, $row): void {
            $account->forceFill(['password' => Hash::make($newPassword)])->save();
            DB::table('password_reset_codes')->where('id', $row->id)->delete();
        });

        return true;
    }

    private function normalize(string $guard, string $identifier): string
    {
        $identifier = trim($identifier);

        return $guard === 'shop_user' ? $identifier : strtolower($identifier);
    }

    private function find(string $guard, string $identifier): ?Authenticatable
    {
        [$model, $column] = self::GUARDS[$guard];

        return $model::where($column, $identifier)->first();
    }

    /** Shop users have no guaranteed personal email, so fall back to their shop's email. */
    private function emailFor(string $guard, Authenticatable $account): ?string
    {
        return $guard === 'shop_user'
            ? ($account->personal_email ?: $account->shop?->email)
            : $account->email;
    }
}
