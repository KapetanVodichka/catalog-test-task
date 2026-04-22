<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Product::query()->exists()) {
            return;
        }

        $categories = Category::all();

        Product::factory()
            ->count(60)
            ->recycle($categories)
            ->create();
    }
}
