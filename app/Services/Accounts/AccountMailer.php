<?php

namespace App\Services\Accounts;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Emails a new account's login details. The caller decides what to do when it returns false. */
class AccountMailer
{
    public function sendLoginDetails(string $to, string $name, string $accountLabel, string $loginUrl, string $username, string $password, string $createdBy): bool
    {
        $body = <<<TEXT
Hello {$name},

{$createdBy} created a {$accountLabel} account for you.

  Login page: {$loginUrl}
  Username:   {$username}
  Password:   {$password}

For your security, please change your password after you log in.
Do not share these details with anyone.
TEXT;

        try {
            Mail::mailer('smtp')->raw($body, fn ($m) => $m->to($to)->subject("Your {$accountLabel} account login details"));
        } catch (\Throwable $e) {
            report($e);
            Log::warning('Account login email failed.', ['account' => $accountLabel]);

            return false;
        }

        return true;
    }
}
