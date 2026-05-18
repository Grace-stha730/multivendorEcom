<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with(['images', 'category', 'vendor']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%')
                ->orWhere('summary', 'like', '%' . $request->search . '%');
        }

        // Category filter
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Price range filter
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->paginate(12);
        $categories = Category::all();

        return view('examples.product-listing', compact('products', 'categories'));
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load(['images', 'category', 'vendor']);

        // Get related products from the same category
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['images'])
            ->limit(4)
            ->get();

        return view('examples.product-detail', compact('product', 'relatedProducts'));
    }

    /**
     * Get products by vendor
     */
    public function vendorProducts($vendorId)
    {
        $products = Product::where('vendor_id', $vendorId)
            ->with(['images', 'category'])
            ->paginate(12);

        return view('examples.product-listing', compact('products'));
    }

    /**
     * Get products by category
     */
    public function categoryProducts($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $products = Product::where('category_id', $categoryId)
            ->with(['images', 'vendor'])
            ->paginate(12);

        return view('examples.product-listing', compact('products', 'category'));
    }
}

