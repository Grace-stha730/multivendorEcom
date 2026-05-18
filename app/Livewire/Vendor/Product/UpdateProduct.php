<?php

namespace App\Livewire\Vendor\Product;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateProduct extends Component
{
    use WithFileUploads;
    public $productId;
    public $name, $stock, $summary, $description, $discount, $category_id, $price;
    public $images = [];
    public $realImg = [];

    #[On('getProductId')]
    public function getProductId($productId)
    {
        $product = Product::find($productId);
        // dd($product);
        $this->productId = $productId;
        $this->name = $product->name;
        $this->stock = $product->stock;
        $this->summary = $product->summary;
        $this->description = $product->description;
        $this->discount = $product->discount;
        $this->category_id = $product->category_id;
        $this->price = $product->price;
        $this->realImg = Image::where('product_id', $productId)->get(['url'])->toArray();

    }

    public function removeImage($index)
    {
        if (!empty($this->images)) {
            unset($this->images[$index]);
            $this->images = array_values($this->images); // re-index array
        } else {
            unset($this->realImg[$index]);
            $this->realImg = array_values($this->realImg); // re-index array
        }
    }

    public function updateProduct()
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
            $product = Product::find($this->productId);
            $product->update([
                'name' => $this->name,
                'stock' => $this->stock,
                'summary' => $this->summary,
                'description' => $this->description,
                'discount' => $this->discount,
                'category_id' => $this->category_id,
                'price' => $this->price,
                'vendor_id' => Auth('vendor')->user()->id,
            ]);
            // Re-sync product images based on current state
            Image::where('product_id', $this->productId)->delete();

            // Keep existing DB images that were not removed in the UI
            if (!empty($this->realImg)) {
                foreach ($this->realImg as $image) {
                    $existingUrl = is_array($image) ? ($image['url'] ?? null) : (string) $image;
                    if ($existingUrl) {
                        Image::create([
                            'product_id' => $product->id,
                            'url' => $existingUrl,
                        ]);
                    }
                }
            }

            // Add any newly uploaded images
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
            return redirect()->route('vendor.product')->with('success', "product update Successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function render()
    {
        return view('livewire.vendor.product.update-product', [
            'categories' => Category::latest()->get(),
        ]);
    }
}
