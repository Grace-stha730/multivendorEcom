<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor = Vendor::firstOrCreate(
            ['shop_email' => 'vendor@example.com'],
            [
                'shop_name' => 'Demo Vendor',
                'owner_name' => 'Demo Owner',
                'shop_province' => 'Bagmati',
                'shop_city' => 'Kathmandu',
                'shop_tole' => 'Thamel',
                'shop_phone' => '9800000000',
                'password' => Hash::make('password'),
                'status' => 1,
            ]
        );

        $category = Category::firstOrCreate(
            ['name' => 'General'],
            [
                'description' => 'General products',
                'vendor_id' => $vendor->id,
            ]
        );

        Product::factory()->count(30)->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
        ]);
    }
}
