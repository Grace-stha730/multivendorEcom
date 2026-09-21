<?php

namespace App\Services\Accounts;

use App\Models\ShopUser;

/** Same username convention as shop creation: firstname.lastname@shopname.com, made unique with a number. */
class ShopUsernameGenerator
{
    public function generate(string $personName, string $shopName): string
    {
        $words = preg_split('/\s+/', trim(mb_strtolower($personName)), -1, PREG_SPLIT_NO_EMPTY);
        $personPart = count($words) <= 2 ? implode('.', $words) : $words[0] . '.' . $words[array_key_last($words)];
        $personPart = preg_replace('/[^\pL\pN.]/u', '', $personPart) ?: 'user';

        $tokens = preg_split('/[\s.]+/u', trim(mb_strtolower($shopName)), -1, PREG_SPLIT_NO_EMPTY);
        $kept = [];
        $brandStarted = false;
        foreach ($tokens as $token) {
            $legal = in_array($token, ['pvt', 'ltd'], true);
            if ($legal && $brandStarted) {
                continue;
            }
            $kept[] = $token;
            $brandStarted = !$legal;
        }
        $shopPart = implode('', array_map(fn (string $t) => preg_replace('/[^\pL\pN]/u', '', $t), $kept)) ?: 'shop';

        $suffix = 0;
        do {
            $username = $personPart . ($suffix ?: '') . '@' . $shopPart . '.com';
            $suffix++;
        } while (ShopUser::where('username', $username)->exists());

        return $username;
    }
}
