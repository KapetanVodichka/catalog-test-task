<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('ru_RU');

        return [
            'name' => 'Категория '.$faker->unique()->numberBetween(1, 9999),
            'description' => 'Описание категории для демонстрационных данных.',
        ];
    }
}
