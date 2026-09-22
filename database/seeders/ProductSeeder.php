<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shop = Shop::firstOrCreate(
            ['email' => 'shop@example.com'],
            [
                'name' => 'Demo Shop', 'owner' => 'Demo Owner', 'contact_number' => '9800000000',
                'province_id' => 1, 'district_id' => 1, 'city' => 'Kathmandu', 'tole' => 'Thamel',
                'status' => 'active',
            ]
        );
        $shopUser = ShopUser::firstOrCreate(['username' => 'demo.shop.user'], ['name' => 'Demo Shop User', 'address' => 'Thamel, Kathmandu', 'contact' => '9800000000', 'password' => Hash::make('password'), 'shop_id' => $shop->id]);

        $category = Category::firstOrCreate(
            ['name' => 'General'],
            [
                'description' => 'General products',
                'shop_id' => $shop->id,
            ]
        );

        Product::factory()->count(30)->create([
            'shop_id' => $shop->id,
            'shop_user_id' => $shopUser->id,
            'category_id' => $category->id,
        ]);
    }
}
