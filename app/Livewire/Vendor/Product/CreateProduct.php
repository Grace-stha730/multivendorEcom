<?php

namespace App\Livewire\Vendor\Product;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product as ProductModal;
use App\Models\ProductVariant;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\AiContentService;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateProduct extends Component
{
    use WithFileUploads;
    public $name, $stock, $summary, $description, $discount, $category_id, $price;
    public $images = [];
    public $productId;
    
    // Product Variants property
    public $variants = [];

    public function generateDescription(AiContentService $ai)
    {
        $shopUserId = Auth::guard('shop_user')->id();
        $key = "ai:product-description:$shopUserId";
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('description', 'AI generation limit reached. Try again in '.RateLimiter::availableIn($key).' seconds.'); return;
        }
        try {
            $category = Category::find($this->category_id);
            $this->description = $ai->generateProductDescription([
                'name' => $this->name, 'category' => $category?->name, 'summary' => $this->summary,
                'price' => $this->price, 'variants' => $this->variants,
            ], $shopUserId);
            RateLimiter::hit($key, 3600);
        } catch (\Throwable $e) { report($e); $this->addError('description', 'Could not generate a description. Please try again.'); }
    }

    public function addVariant()
    {
        $this->variants[] = [
            'attribute_name' => '',
            'attribute_value' => '',
            'price_extra' => 0,
            'stock' => 0,
        ];
    }

    public function removeVariant($index)
    {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

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
            'variants' => 'array',
            'variants.*.attribute_name' => 'nullable|string|max:100',
            'variants.*.attribute_value' => 'nullable|string|max:100',
            'variants.*.price_extra' => 'nullable|numeric',
            'variants.*.stock' => 'nullable|integer|min:0',
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
                'shop_id' => Auth::guard('shop_user')->user()->shop_id,
                'shop_user_id' => Auth::guard('shop_user')->id(),
            ];

            if (!empty($this->discount) && $this->discount > 0) {
                $productData['discount'] = $this->discount;
                $productData['discount_amount'] = ($this->price * $this->discount) / 100;
            }


            $product = ProductModal::create($productData);

            $product->variants()->createMany($this->variantPayload());

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
            return redirect()->route('shop-user.product')->with('success', "Product created successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    private function variantPayload(): array
    {
        return collect($this->variants)
            ->filter(fn ($variant) => filled($variant['attribute_name'] ?? null))
            ->map(fn ($variant) => [
                'attribute_name' => trim($variant['attribute_name']),
                'attribute_value' => trim($variant['attribute_value'] ?? ''),
                'price_extra' => $variant['price_extra'] ?? 0,
                'stock' => $variant['stock'] ?? 0,
            ])->values()->all();
    }
    public function render()
    {
        return view('livewire.vendor.product.create-product', [
            'categories' => Category::latest()->get(),
        ]);
    }
}
