<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('ru_RU');
        $names = [
            'Смартфон Nova X',
            'Ноутбук Atlas Pro',
            'Наушники Wave Air',
            'Игровая мышь Falcon',
            'Кофемашина Barista One',
            'Фитнес-браслет Pulse Fit',
            'Увлажнитель воздуха Breeze',
            'Рюкзак Urban Go',
            'Настольная лампа Lumen',
            'Планшет Vision Tab',
            'Смарт-часы TimeGo',
            'Портативная колонка Boom Mini',
            'Внешний SSD DriveMax',
            'Электрочайник Heat Pro',
            'Кресло Ergo Comfort',
        ];
        $descriptions = [
            'Практичный товар для ежедневного использования с надежной сборкой.',
            'Хороший баланс между ценой, производительностью и удобством.',
            'Подходит для дома и офиса, выполнен из качественных материалов.',
            'Современный дизайн, стабильная работа и понятное управление.',
            'Отличный выбор для пользователей, которым важны комфорт и надежность.',
        ];

        return [
            'name' => $faker->randomElement($names),
            'description' => $faker->randomElement($descriptions),
            'price' => $faker->randomFloat(2, 10, 5000),
            'category_id' => Category::factory(),
        ];
    }
}
