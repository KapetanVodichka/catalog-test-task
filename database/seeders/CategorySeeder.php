<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Электроника',
                'description' => 'Смартфоны, ноутбуки, гаджеты и аксессуары.',
            ],
            [
                'name' => 'Дом и интерьер',
                'description' => 'Мебель, декор и товары для кухни.',
            ],
            [
                'name' => 'Спорт',
                'description' => 'Инвентарь и товары для активного образа жизни.',
            ],
            [
                'name' => 'Книги',
                'description' => 'Художественная, научно-популярная и учебная литература.',
            ],
            [
                'name' => 'Красота',
                'description' => 'Уходовая косметика, макияж и средства личной гигиены.',
            ],
            [
                'name' => 'Игрушки',
                'description' => 'Игры и игрушки для детей разных возрастов.',
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
