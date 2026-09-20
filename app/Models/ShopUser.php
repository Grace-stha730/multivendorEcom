<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class ShopUser extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // Shop roles/permissions live in their own pool, keyed by this guard.
    protected $guard_name = 'shop_user';

    protected static function booted(): void
    {
        // The first user created for a shop becomes its owner - no matter which screen created the shop.
        static::created(function (ShopUser $user): void {
            $hasOthers = static::where('shop_id', $user->shop_id)->where('id', '!=', $user->id)->exists();
            if ($hasOthers) {
                return;
            }

            $role = Role::where('name', config('access.default_shop_owner_role'))
                ->where('guard_name', config('access.guards.shop_user'))
                ->first();

            $role
                ? $user->assignRole($role)
                : Log::warning('Owner role missing; run RolesAndPermissionsSeeder.', ['shop_user_id' => $user->id]);
        });
    }
    protected $fillable = [
        'name',
        'username',
        'personal_email',
        'address',
        'password',
        'contact',
        'image',
        'pan_number',
        'shop_id',
    ];

    public function products(){
        return $this->hasMany(Product::class, 'shop_user_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
