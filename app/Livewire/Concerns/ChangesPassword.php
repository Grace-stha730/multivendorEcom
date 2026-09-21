<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/** The Security card: change your own password. Asks for the current one first. Used by all three settings pages. */
trait ChangesPassword
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /** The logged-in account whose password is being changed. */
    abstract protected function passwordOwner(): Model;

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'password.different' => 'The new password must be different from your current password.',
        ]);

        $account = $this->passwordOwner();

        if (!Hash::check($this->current_password, $account->password)) {
            $this->addError('current_password', 'The current password is incorrect.');

            return;
        }

        $account->forceFill(['password' => Hash::make($this->password)])->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->resetValidation();
        $this->success('Password changed', 'Use your new password the next time you log in.', 'toast-bottom toast-end');
    }
}
