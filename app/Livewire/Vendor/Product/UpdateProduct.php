<?php

namespace App\Livewire\Vendor\Product;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\AiContentService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateProduct extends Component
{
    use \App\Livewire\Concerns\AuthorizesPermissions;
    use WithFileUploads;
    public $productId;
    public $name, $stock, $summary, $description, $discount, $category_id, $price;
    public $images = [];
    public $realImg = [];
    
    // Product Variants property
    public $variants = [];

    public function generateDescription(AiContentService $ai)
    {
        $this->authorizeShop('product-edit');
        $shopUserId = Auth::guard('shop_user')->id(); $key = "ai:product-description:$shopUserId";
        if (RateLimiter::tooManyAttempts($key, 10)) { $this->addError('description', 'AI generation limit reached. Try again later.'); return; }
        try {
            $this->description = $ai->generateProductDescription([
                'name' => $this->name, 'category' => Category::find($this->category_id)?->name,
                'summary' => $this->summary, 'price' => $this->price, 'variants' => $this->variants,
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

    #[On('getProductId')]
    public function getProductId($productId)
    {
        $this->authorizeShop('product-edit');
        $product = Product::where('shop_id', Auth::guard('shop_user')->user()->shop_id)->findOrFail($productId);
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
        $this->variants = ProductVariant::where('product_id', $productId)
            ->get(['id', 'attribute_name', 'attribute_value', 'price_extra', 'stock'])
            ->toArray();

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
        $this->authorizeShop('product-edit');
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
            'variants.*.id' => 'nullable|integer',
            'variants.*.attribute_name' => 'nullable|string|max:100',
            'variants.*.attribute_value' => 'nullable|string|max:100',
            'variants.*.price_extra' => 'nullable|numeric',
            'variants.*.stock' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::where('shop_id', Auth::guard('shop_user')->user()->shop_id)->findOrFail($this->productId);
            $product->update([
                'name' => $this->name,
                'stock' => $this->stock,
                'summary' => $this->summary,
                'description' => $this->description,
                'discount' => $this->discount,
                'discount_amount' => $this->discount > 0 ? ($this->price * $this->discount) / 100 : null,
                'category_id' => $this->category_id,
                'price' => $this->price,
                'shop_id' => Auth::guard('shop_user')->user()->shop_id,
                'shop_user_id' => Auth::guard('shop_user')->id(),
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

            $this->syncVariants($product);

            DB::commit();
            $this->reset();
            return redirect()->route('shop-user.product')->with('success', "Product updated successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function syncVariants(Product $product): void
    {
        $submittedIds = collect($this->variants)->pluck('id')->filter()->map(fn ($id) => (int) $id);
        $product->variants()->whereNotIn('id', $submittedIds)->delete();

        foreach ($this->variants as $variant) {
            if (!filled($variant['attribute_name'] ?? null)) continue;
            $data = [
                'attribute_name' => trim($variant['attribute_name']),
                'attribute_value' => trim($variant['attribute_value'] ?? ''),
                'price_extra' => $variant['price_extra'] ?? 0,
                'stock' => $variant['stock'] ?? 0,
            ];
            if (!empty($variant['id'])) $product->variants()->whereKey($variant['id'])->update($data);
            else $product->variants()->create($data);
        }
    }
    public function render()
    {
        return view('livewire.vendor.product.update-product', [
            'categories' => Category::latest()->get(),
        ]);
    }
}
