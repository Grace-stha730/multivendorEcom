<?php

namespace App\Livewire\Admin;

use App\Models\Coupon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manage Coupons')]
#[Layout('components.layouts.admin')]
class Coupons extends Component
{
    public $code, $description, $type = 'fixed', $value, $min_order_amount = 0, $usage_limit = 1, $min_item_price = 0, $starts_at, $expires_at;
    public $is_active = true;
    public $editingId = null;

    protected $rules = [
        'code' => 'required|string|max:50',
        'description' => 'nullable|string|max:1000',
        'type' => 'required|in:fixed,percent',
        'value' => 'required|numeric|min:0.01',
        'min_order_amount' => 'required|numeric|min:0',
        'usage_limit' => 'required|integer|min:1',
        'min_item_price' => 'required|numeric|min:0',
        'starts_at' => 'nullable|date',
        'expires_at' => 'nullable|date|after_or_equal:starts_at',
    ];

    public function saveCoupon()
    {
        $this->validate();

        $code = strtoupper(trim($this->code));

        if ($this->editingId) {
            $coupon = Coupon::findOrFail($this->editingId);
            $coupon->update([
                'code' => $code,
                'description' => $this->description,
                'type' => $this->type,
                'value' => $this->value,
                'min_order_amount' => $this->min_order_amount,
                'usage_limit' => $this->usage_limit,
                'min_item_price' => $this->min_item_price,
                'is_active' => $this->is_active,
                'starts_at' => $this->starts_at ?: null,
                'expires_at' => $this->expires_at ?: null,
            ]);
            session()->flash('success', 'Coupon updated successfully');
        } else {
            Coupon::create([
                'code' => $code,
                'description' => $this->description,
                'type' => $this->type,
                'value' => $this->value,
                'min_order_amount' => $this->min_order_amount,
                'usage_limit' => $this->usage_limit,
                'min_item_price' => $this->min_item_price,
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
        $coupon = Coupon::findOrFail($id);
        $this->editingId = $coupon->id;
        $this->code = $coupon->code;
        $this->description = $coupon->description;
        $this->type = $coupon->type;
        $this->value = $coupon->value;
        $this->min_order_amount = $coupon->min_order_amount;
        $this->usage_limit = $coupon->usage_limit;
        $this->min_item_price = $coupon->min_item_price;
        $this->is_active = $coupon->is_active;
        $this->starts_at = $coupon->starts_at ? $coupon->starts_at->format('Y-m-d') : null;
        $this->expires_at = $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : null;
    }

    public function toggleStatus($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);
        session()->flash('success', 'Coupon status updated');
    }

    public function deleteCoupon($id)
    {
        Coupon::findOrFail($id)->delete();
        session()->flash('success', 'Coupon deleted');
    }

    public function resetForm()
    {
        $this->reset(['code', 'description', 'type', 'value', 'min_order_amount', 'usage_limit', 'min_item_price', 'starts_at', 'expires_at', 'is_active', 'editingId']);
        $this->type = 'fixed';
        $this->min_order_amount = 0;
        $this->usage_limit = 1;
        $this->min_item_price = 0;
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.admin.coupons', [
            'coupons' => Coupon::latest()->get(),
        ]);
    }
}
