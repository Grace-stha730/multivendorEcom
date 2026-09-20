<?php

namespace App\Livewire\Concerns;

/**
 * Livewire actions are separate HTTP requests that skip route middleware,
 * so every state-changing action re-checks its permission with these helpers.
 */
trait AuthorizesPermissions
{
    protected function authorizeAdmin(string $permission): void
    {
        abort_unless(authorizeUserCheck($permission, 'admin'), 403, 'You do not have permission to do that.');
    }

    protected function authorizeShop(string $permission): void
    {
        abort_unless(authorizeUserCheck($permission, 'shop_user'), 403, 'You do not have permission to do that.');
    }
}
