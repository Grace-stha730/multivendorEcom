<?php

namespace App\Livewire\User;

use App\Models\Cart;
use App\Models\Cart_items;
use App\Models\Category;
use App\Models\ProductCollection;
use App\Models\productRating;
use App\Services\Search\ClusteredProductSearch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Product as modalProduct;
use Livewire\Attributes\Title;

#[Title(content: 'Product')]
#[Layout('components/layouts/user')]
class Product extends Component
{
    public $search = "", $category, $searchAlgorithm = 'kmeans', $collectionId;

    public function AddToCart($id)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')
                ->with('error', 'Please login first to add product to cart.');
        }

        DB::beginTransaction();

        try {
            $product = modalProduct::find($id);
            $userId = Auth::guard('web')->user()->id;
            $cart = Cart::firstOrCreate(['user_id' => $userId]);

            $cartItem = Cart_items::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            $price = $product->discount
                ? $product->price - $product->discount_amount
                : $product->price;

            if ($cartItem) {
                DB::rollBack();
                return redirect()->route('user.product')->with('error', 'This is product is already in cart');
            } else {
                Cart_items::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $price,
                    'sub_total' => $price,
                ]);

                DB::commit();
                return redirect()->route('user.product')->with('success', 'Product added to cart');
            }


        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again.');
        }
    }

    public function addToCollection($productId)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login')
                ->with('error', 'Please login first to save products.');
        }

        $this->validate([
            'collectionId' => 'required|exists:product_collections,id',
        ]);

        $collection = ProductCollection::where('user_id', Auth::guard('web')->id())
            ->findOrFail($this->collectionId);

        $collection->products()->syncWithoutDetaching([$productId]);

        session()->flash('success', 'Product saved to collection.');
    }

    public function render()
    {
        $searchGroups = app(ClusteredProductSearch::class)
            ->search($this->search, $this->searchAlgorithm);
        $products = collect($searchGroups)->flatMap(fn ($group) => $group['products'])->values();
        $categories = Category::all();
        $collections = Auth::guard('web')->check()
            ? ProductCollection::where('user_id', Auth::guard('web')->id())->latest()->get()
            : collect();

        return view('livewire.user.product', [
            'products' => $products,
            'searchGroups' => $searchGroups,
            'categories' => $categories,
            'collections' => $collections,
        ]);
    }
}
