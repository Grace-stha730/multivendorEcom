<?php

namespace App\Livewire\Vendor;

use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Vendor Coupons')]
class Coupons extends Component
{
    public $code, $type = 'fixed', $value, $min_order_amount = 0, $starts_at, $expires_at;
    public $is_active = true;
    public $editingId = null;

    protected $rules = [
        'code' => 'required|string|max:50',
        'type' => 'required|in:fixed,percent',
        'value' => 'required|numeric|min:0.01',
        'min_order_amount' => 'required|numeric|min:0',
        'starts_at' => 'nullable|date',
        'expires_at' => 'nullable|date|after_or_equal:starts_at',
    ];

    public function saveCoupon()
    {
        $this->validate();
        $shopId = Auth::guard('shop_user')->user()->shop_id;
        $code = strtoupper(trim($this->code));

        if ($this->editingId) {
            $coupon = Coupon::where('shop_id', $shopId)->findOrFail($this->editingId);
            $coupon->update([
                'code' => $code,
                'type' => $this->type,
                'value' => $this->value,
                'min_order_amount' => $this->min_order_amount,
                'is_active' => $this->is_active,
                'starts_at' => $this->starts_at ?: null,
                'expires_at' => $this->expires_at ?: null,
            ]);
            session()->flash('success', 'Coupon updated successfully');
        } else {
            Coupon::create([
                'shop_id' => $shopId,
                'code' => $code,
                'type' => $this->type,
                'value' => $this->value,
                'min_order_amount' => $this->min_order_amount,
                'is_active' => $this->is_active,
                'starts_at' => $this->starts_at ?: null,
                'expires_at' => $this->expires_at ?: null,
            ]);
            session()->flash('success', 'Coupon created successfully');
        }

        $this->resetForm();
    }

    public function editCoupon($id)
    {
        $shopId = Auth::guard('shop_user')->user()->shop_id;
        $coupon = Coupon::where('shop_id', $shopId)->findOrFail($id);
        $this->editingId = $coupon->id;
        $this->code = $coupon->code;
        $this->type = $coupon->type;
        $this->value = $coupon->value;
        $this->min_order_amount = $coupon->min_order_amount;
        $this->is_active = $coupon->is_active;
        $this->starts_at = $coupon->starts_at ? $coupon->starts_at->format('Y-m-d') : null;
        $this->expires_at = $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : null;
    }

    public function toggleStatus($id)
    {
        $shopId = Auth::guard('shop_user')->user()->shop_id;
        $coupon = Coupon::where('shop_id', $shopId)->findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);
        session()->flash('success', 'Coupon status updated');
    }

    public function deleteCoupon($id)
    {
        $shopId = Auth::guard('shop_user')->user()->shop_id;
        Coupon::where('shop_id', $shopId)->findOrFail($id)->delete();
        session()->flash('success', 'Coupon deleted');
    }

    public function resetForm()
    {
        $this->reset(['code', 'type', 'value', 'min_order_amount', 'starts_at', 'expires_at', 'is_active', 'editingId']);
    }

    public function render()
    {
        $shopId = Auth::guard('shop_user')->user()->shop_id;
        return view('livewire.vendor.coupons', [
            'coupons' => Coupon::where('shop_id', $shopId)->latest()->get(),
        ]);
    }
}
