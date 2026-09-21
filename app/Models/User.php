<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'photo',
        'token',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** The customer's address book: one real address plus any number of shipping addresses. */
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function realAddress()
    {
        return $this->hasOne(UserAddress::class)->where('address_category', UserAddress::REAL);
    }

    /** The address checkout pre-selects (default, else real, else any complete one). */
    public function defaultAddress(): ?UserAddress
    {
        return app(\App\Services\UserAddressService::class)->preferred($this);
    }

    public function carts(){
        return $this->hasMany(Cart::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function productRating(){
        return $this->hasOne(productRating::class);
    }

    public function productRatings()
    {
        return $this->hasMany(productRating::class);
    }

    public function wishlists(){
        return $this->hasMany(Wishlist::class);
    }

    public function collectedCoupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_user')
            ->withPivot(['collected_at'])
            ->withTimestamps();
    }
}
