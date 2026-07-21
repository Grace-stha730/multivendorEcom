<?php

namespace App\Livewire\Admin;

use App\Models\VendorPayout;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Vendor Payout Requests')]
#[Layout('components.layouts.admin')]
class Payouts extends Component
{
    public function approvePayout($id)
    {
        $payout = VendorPayout::findOrFail($id);
        $payout->update(['status' => 'Paid']);
        session()->flash('success', 'Payout approved and marked as Paid');
    }

    public function rejectPayout($id)
    {
        $payout = VendorPayout::findOrFail($id);
        $payout->update(['status' => 'Rejected']);
        session()->flash('success', 'Payout request rejected');
    }

    public function render()
    {
        return view('livewire.admin.payouts', [
            'payouts' => VendorPayout::with('vendor')->latest()->get(),
        ]);
    }
}
