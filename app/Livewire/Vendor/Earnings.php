<?php

namespace App\Livewire\Vendor;

use App\Models\VendorOrder;
use App\Models\VendorPayout;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Vendor Earnings & Payouts')]
class Earnings extends Component
{
    public $amount, $payment_method = 'Bank Transfer', $account_details, $notes;

    protected $rules = [
        'amount' => 'required|numeric|min:100',
        'payment_method' => 'required|string',
        'account_details' => 'required|string|max:255',
        'notes' => 'nullable|string|max:500',
    ];

    public function requestPayout($availableBalance)
    {
        $this->validate();
        $vendorId = Auth::guard('vendor')->id();

        if ($this->amount > $availableBalance) {
            $this->addError('amount', 'Payout amount cannot exceed available balance of Rs. ' . number_format($availableBalance));
            return;
        }

        VendorPayout::create([
            'vendor_id' => $vendorId,
            'amount' => $this->amount,
            'status' => 'Pending',
            'payment_method' => $this->payment_method,
            'account_details' => $this->account_details,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Payout request submitted successfully!');
        $this->reset(['amount', 'account_details', 'notes']);
    }

    public function render()
    {
        $vendorId = Auth::guard('vendor')->id();

        // 1. Gross Sales from Delivered/Completed vendor orders
        $deliveredOrders = VendorOrder::where('vendor_id', $vendorId)
            ->where('status', 'Delivered')
            ->get();

        $grossSales = $deliveredOrders->sum('subtotal');
        $commissionFee = $grossSales * 0.10; // 10% platform fee
        $netEarnings = $grossSales - $commissionFee;

        // 2. Payouts
        $payouts = VendorPayout::where('vendor_id', $vendorId)->latest()->get();
        $totalPaidOut = $payouts->whereIn('status', ['Approved', 'Paid'])->sum('amount');
        $pendingPayouts = $payouts->where('status', 'Pending')->sum('amount');
        $availableBalance = max(0, $netEarnings - $totalPaidOut - $pendingPayouts);

        return view('livewire.vendor.earnings', [
            'grossSales' => $grossSales,
            'commissionFee' => $commissionFee,
            'netEarnings' => $netEarnings,
            'totalPaidOut' => $totalPaidOut,
            'pendingPayouts' => $pendingPayouts,
            'availableBalance' => $availableBalance,
            'payouts' => $payouts,
        ]);
    }
}
