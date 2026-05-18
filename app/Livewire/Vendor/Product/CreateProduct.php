<?php

namespace App\Livewire\Vendor\Product;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product as ProductModal;
use Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateProduct extends Component
{
    use WithFileUploads;
    public $name, $stock, $summary, $description, $discount, $category_id, $price;
    public $images = [];
    public $productId;

    public function removeImage($index)
    {
        unset($this->images[$index]);
        $this->images = array_values($this->images); // re-index array
    }



    public function saveProduct()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required',
            'summary' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'discount' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'images.*' => 'nullable|image', // each image must be an image file and max 1MB
        ]);

        DB::beginTransaction();
        
        try {
            $productData = [
                'name' => $this->name,
                'stock' => $this->stock,
                'summary' => $this->summary,
                'description' => $this->description,
                'category_id' => $this->category_id,
                'price' => $this->price,
                'vendor_id' => Auth('vendor')->user()->id,
            ];

            if (!empty($this->discount) && $this->discount > 0) {
                $productData['discount'] = $this->discount;
                $productData['discount_amount'] = ($this->price * $this->discount) / 100;
            }


            $product = ProductModal::create($productData);

            if (!empty($this->images)) {
                foreach ($this->images as $image) {
                    $imagePath = $image->store('products', 'public');
                    Image::create([
                        'product_id' => $product->id,
                        'url' => $imagePath,
                    ]);
                }
            }
            DB::commit();
            $this->reset();
            return redirect()->route('vendor.product')->with('success', "product Create Successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }
    public function render()
    {
        return view('livewire.vendor.product.create-product', [
            'categories' => Category::latest()->get(),
        ]);
    }
}
