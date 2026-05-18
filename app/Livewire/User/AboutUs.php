<?php

namespace App\Livewire\User;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title(content: 'About Us')]
#[Layout('components.layouts.user')]
class AboutUs extends Component
{
    public function render()
    {
        return view('livewire.user.about-us');
    }
}
