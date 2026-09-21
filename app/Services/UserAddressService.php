<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * The address book rules in one place:
 *  - A user's FIRST address is their REAL address (exactly one, enforced by the database too).
 *  - Every later address is a SHIPPING address (any number).
 *  - One default address per user (the one checkout pre-selects). The first address is the default.
 *  - The real address cannot be deleted while other addresses exist. Deleting the default promotes another.
 */
class UserAddressService
{
    public function create(User $user, array $data, bool $makeDefault = false): UserAddress
    {
        return DB::transaction(function () use ($user, $data, $makeDefault) {
            $isFirst = !$user->addresses()->exists();

            try {
                $address = $this->insert($user, $data, $isFirst ? UserAddress::REAL : UserAddress::SHIPPING);
            } catch (UniqueConstraintViolationException) {
                // Two "first" addresses raced each other; the database allowed only one real address.
                $address = $this->insert($user, $data, UserAddress::SHIPPING);
                $isFirst = false;
            }

            if ($isFirst || $makeDefault) {
                $this->makeDefault($address);
            }

            return $address->fresh();
        });
    }

    public function update(UserAddress $address, array $data, bool $makeDefault = false): UserAddress
    {
        return DB::transaction(function () use ($address, $data, $makeDefault) {
            // The category is never changed by an edit.
            $address->update($this->clean($data));

            if ($makeDefault) {
                $this->makeDefault($address);
            }

            return $address->fresh();
        });
    }

    public function makeDefault(UserAddress $address): void
    {
        DB::transaction(function () use ($address) {
            UserAddress::where('user_id', $address->user_id)->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });
    }

    /** @return string|null an error message when the delete is not allowed, null on success */
    public function delete(UserAddress $address): ?string
    {
        $hasOthers = UserAddress::where('user_id', $address->user_id)->where('id', '!=', $address->id)->exists();

        if ($address->isReal() && $hasOthers) {
            return 'Your real address cannot be deleted while you have other addresses. Edit it instead, or delete your shipping addresses first.';
        }

        DB::transaction(function () use ($address) {
            $wasDefault = $address->is_default;
            $userId = $address->user_id;
            $address->delete();

            if ($wasDefault) {
                $next = UserAddress::where('user_id', $userId)
                    ->orderByRaw("address_category = 'real' DESC")->orderBy('id')->first();
                $next?->update(['is_default' => true]);
            }
        });

        return null;
    }

    /** The address checkout should pre-select: the default if it is usable, else the real one, else any usable one. */
    public function preferred(User $user): ?UserAddress
    {
        return $user->addresses()
            ->whereNotNull('province_id')->whereNotNull('district_id')
            ->orderByDesc('is_default')->orderByRaw("address_category = 'real' DESC")->orderBy('id')
            ->first();
    }

    private function insert(User $user, array $data, string $category): UserAddress
    {
        return UserAddress::create($this->clean($data) + [
            'user_id' => $user->id,
            'address_category' => $category,
            'is_default' => false,
        ]);
    }

    private function clean(array $data): array
    {
        $office = ($data['address_type'] ?? UserAddress::HOME) === UserAddress::OFFICE;

        return [
            'province_id' => $data['province_id'],
            'district_id' => $data['district_id'],
            'city' => trim($data['city']),
            'tole' => trim($data['tole']),
            'contact' => trim($data['contact']),
            'receiver_name' => trim($data['receiver_name']),
            'address_type' => $office ? UserAddress::OFFICE : UserAddress::HOME,
            'office_start_time' => $office ? $data['office_start_time'] : null,
            'office_end_time' => $office ? $data['office_end_time'] : null,
        ];
    }
}
