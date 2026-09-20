<?php

namespace App\Models;

use App\Enums\DepartmentTypeState;
use App\Enums\RoleTypeState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // Admin roles/permissions live in their own pool, keyed by this guard.
    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'role',
        'department',
        'phone',
        'address',
        'is_active',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'role' => RoleTypeState::class,
            'department' => DepartmentTypeState::class,
        ];
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
