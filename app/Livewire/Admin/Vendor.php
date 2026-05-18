<?php

namespace App\Livewire\Admin;

use App\Models\VendorOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Vendor as modelVendor
;

#[Layout('components.layouts.admin')]
#[Title('Vendor')]
class Vendor extends Component
{
    public function deleteVendor($id){
        $vendor = modelVendor::find($id);
        $vendorOrder = VendorOrder::where('vendor_id', $vendor->id)->first();
        // dd($vendorOrder);
        if($vendorOrder){
            return redirect()->route('admin.vendor')->with('error','this vendor has an order');
        }

        else{
            $vendor->delete();
            return redirect()->route('admin.vendor')->with('success','vendor successfully');
        }

    }
    public function render()
    {
        return view('livewire.admin.vendor',[
            'vendors' =>  modelVendor::latest()->paginate(30),
        ]);
    }
}
