<?php

namespace App\Livewire\User;

use App\Models\Order;
use App\Models\Order_item;
use App\Models\productRating;
use App\Models\RatingImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.user')]
#[Title('Review')]

class Review extends Component
{
    use WithFileUploads;

    public $order;
    public $newRate = [];   // per product rating
    public $images = [];    // per product images
    public $message = [];   // per product message

    public function mount($id)
    {
        $this->order = Order::with('orderItems', 'orderItems.product')->findOrFail($id);
    }

    public function updateRate($productId, $value)
    {
        $this->newRate[$productId] = $value;
    }

    public function removeImage($productId, $index)
    {
        unset($this->images[$productId][$index]);
        $this->images[$productId] = array_values($this->images[$productId]);
    }

    public function submit()
    {
        DB::beginTransaction();

        try {
            foreach ($this->order->orderItems  as $index => $item) {
                $productId = $item->product->id;

                // Create product review
                $review = ProductRating::create([
                    'product_id' => $productId,
                    'user_id' => Auth::id(),
                    'rating' => $this->newRate[$productId] ?? 0,
                    'message' => $this->message[$productId] ?? '',
                ]);

                // Save uploaded images
                if (!empty($this->images[$productId])) {
                    foreach ($this->images[$productId] as $image) {
                        $path = $image->store('reviews', 'public');

                        RatingImage::create([
                            'product_rating_id' => $review->id,
                            'images' => $path,
                        ]);
                    }
                }

                $item->update([
                    'is_rate' => true,
                ]);
            }

            DB::commit();
            return redirect()
                ->route('user.order')
                ->with('success', 'Thank you for your valuable feedback! Your reviews have been submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('user.order')
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.user.review', [
            'order' => $this->order,
        ]);
    }
}