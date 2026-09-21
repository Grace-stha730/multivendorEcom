<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    public const REAL = 'real';
    public const SHIPPING = 'shipping';
    public const HOME = 'home';
    public const OFFICE = 'office';

    protected $fillable = [
        'user_id', 'province_id', 'district_id', 'city', 'tole', 'contact', 'receiver_name',
        'address_type', 'office_start_time', 'office_end_time', 'address_category', 'is_default',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function isReal(): bool
    {
        return $this->address_category === self::REAL;
    }

    public function isOffice(): bool
    {
        return $this->address_type === self::OFFICE;
    }

    /** Addresses migrated from the old users table may lack province/district until the customer completes them. */
    public function isComplete(): bool
    {
        return $this->province_id !== null && $this->district_id !== null;
    }

    /** "Thamel, Kathmandu, Kathmandu District, Bagmati" */
    public function fullAddress(): string
    {
        return collect([$this->tole, $this->city, $this->district?->name, $this->province?->name])->filter()->implode(', ');
    }

    /** "9:00 AM - 5:00 PM" (office addresses only) */
    public function officeHours(): ?string
    {
        return $this->isOffice() ? self::formatHours($this->office_start_time, $this->office_end_time) : null;
    }

    public static function formatHours(?string $start, ?string $end): ?string
    {
        if (!$start || !$end) {
            return null;
        }

        return \Carbon\Carbon::parse($start)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($end)->format('g:i A');
    }

    /** "HH:MM" for <input type="time">. */
    public function timeForInput(?string $value): ?string
    {
        return $value ? substr($value, 0, 5) : null;
    }

    /**
     * The address exactly as it is right now, as order columns. Orders store a COPY of these values, so later
     * edits or deletion of this address never change a past order. `province` (text) and `phone` reuse the
     * order's existing legacy columns so old screens and invoices keep working.
     */
    public function toOrderSnapshot(): array
    {
        $this->loadMissing(['province', 'district']);

        return [
            'user_address_id' => $this->id,
            'receiver_name' => $this->receiver_name,
            'phone' => $this->contact,
            'province' => $this->province?->name ?? '',
            'province_id' => $this->province_id,
            'district_id' => $this->district_id,
            'city' => $this->city,
            'tole' => $this->tole,
            'address_type' => $this->address_type,
            'office_start_time' => $this->isOffice() ? $this->office_start_time : null,
            'office_end_time' => $this->isOffice() ? $this->office_end_time : null,
        ];
    }
}
