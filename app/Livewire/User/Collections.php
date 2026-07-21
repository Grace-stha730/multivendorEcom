<?php

namespace App\Livewire\User;

use App\Models\ProductCollection;
use App\Services\Search\ClusteredProductSearch;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title(content: 'Collections')]
#[Layout('components/layouts/user')]
class Collections extends Component
{
    public $name = '';
    public $description = '';
    public $isPublic = true;
    public $selectedCollectionId;

    public function createCollection()
    {
        $this->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'isPublic' => 'boolean',
        ]);

        ProductCollection::create([
            'user_id' => Auth::guard('web')->id(),
            'name' => $this->name,
            'description' => $this->description,
            'is_public' => $this->isPublic,
        ]);

        $this->reset(['name', 'description']);
        $this->isPublic = true;
        session()->flash('success', 'Collection created.');
    }

    public function toggleStar($collectionId)
    {
        $collection = ProductCollection::where('is_public', true)
            ->where('user_id', '!=', Auth::guard('web')->id())
            ->findOrFail($collectionId);

        $star = $collection->stars()->where('user_id', Auth::guard('web')->id())->first();

        if ($star) {
            $star->delete();
            session()->flash('error', 'Collection unstarred.');
            return;
        }

        $collection->stars()->create(['user_id' => Auth::guard('web')->id()]);
        session()->flash('success', 'Collection starred.');
    }

    public function generateProductOfTheDay()
    {
        $this->validate([
            'selectedCollectionId' => 'required|exists:product_collections,id',
        ]);

        $collection = ProductCollection::where('user_id', Auth::guard('web')->id())
            ->findOrFail($this->selectedCollectionId);
        $topProduct = collect(app(ClusteredProductSearch::class)->search('', 'kmeans'))
            ->flatMap(fn ($group) => $group['products'])
            ->first();

        if (!$topProduct) {
            session()->flash('error', 'No product is available for Product of the Day.');
            return;
        }

        $collection->products()->syncWithoutDetaching([$topProduct->id]);
        session()->flash('success', $topProduct->name . ' added as Product of the Day.');
    }

    public function render()
    {
        $userId = Auth::guard('web')->id();

        return view('livewire.user.collections', [
            'myCollections' => ProductCollection::where('user_id', $userId)
                ->with(['products.firstImage', 'stars'])
                ->latest()
                ->get(),
            'publicCollections' => ProductCollection::where('is_public', true)
                ->where('user_id', '!=', $userId)
                ->with(['user', 'products.firstImage', 'stars'])
                ->withCount('stars')
                ->latest()
                ->get(),
        ]);
    }
}
