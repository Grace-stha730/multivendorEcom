<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopRegistration extends Model
{
    public const PENDING = 'PENDING';
    public const APPROVED = 'APPROVED';
    public const REJECTED = 'REJECTED';

    protected $fillable = [
        'shop_name', 'owner', 'email', 'pan_number', 'contact_number', 'province_id',
        'district_id', 'city', 'tole', 'status', 'is_email_verified',
        'email_verification_code', 'email_verification_code_expires_at',
        'email_verification_code_sent_at', 'rejection_reason',
    ];

    protected $hidden = ['email_verification_code'];

    protected function casts(): array
    {
        return [
            'is_email_verified' => 'boolean',
            'email_verification_code_expires_at' => 'datetime',
            'email_verification_code_sent_at' => 'datetime',
        ];
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
