<?php

namespace App\Livewire\Admin;

use App\Models\StorePolicy;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Store Policies')]
class StorePolicies extends Component
{
    public string $shipping = '';
    public string $returns = '';
    public string $general = '';

    public function mount(): void
    {
        $policies = StorePolicy::pluck('value', 'key');
        $this->shipping = $policies['shipping'] ?? '';
        $this->returns = $policies['returns'] ?? '';
        $this->general = $policies['general'] ?? '';
    }

    public function save(): void
    {
        $this->validate(['shipping' => 'nullable|string|max:5000', 'returns' => 'nullable|string|max:5000', 'general' => 'nullable|string|max:5000']);
        foreach (['shipping', 'returns', 'general'] as $key) StorePolicy::updateOrCreate(['key' => $key], ['value' => $this->{$key}, 'updated_at' => now()]);
        session()->flash('success', 'AI support policies updated.');
    }

    public function render() { return view('livewire.admin.store-policies'); }
}
