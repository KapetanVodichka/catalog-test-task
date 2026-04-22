<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_get_paginated_products_with_category(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(13)->for($category)->create();

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('meta.per_page', 12)
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'description',
                        'price',
                        'category_id',
                        'category' => ['id', 'name', 'description', 'created_at', 'updated_at'],
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_public_can_filter_products_by_category(): void
    {
        $firstCategory = Category::factory()->create();
        $secondCategory = Category::factory()->create();

        Product::factory()->count(2)->for($firstCategory)->create();
        Product::factory()->count(3)->for($secondCategory)->create();

        $response = $this->getJson('/api/products?category_id='.$firstCategory->id.'&per_page=10');

        $response->assertOk()->assertJsonCount(2, 'data');

        $categoryIds = collect($response->json('data'))->pluck('category_id')->unique()->all();

        $this->assertSame([$firstCategory->id], $categoryIds);
    }

    public function test_public_can_search_products_by_name_and_description(): void
    {
        $category = Category::factory()->create();

        Product::factory()->for($category)->create([
            'name' => 'Фонарь туристический',
            'description' => 'Надежный фонарь для походов.',
        ]);

        Product::factory()->for($category)->create([
            'name' => 'Рюкзак походный',
            'description' => 'Есть крепление для фонаря.',
        ]);

        Product::factory()->for($category)->create([
            'name' => 'Кружка стальная',
            'description' => 'Для горячих напитков.',
        ]);

        $response = $this->getJson('/api/products?search=фонар&per_page=15');

        $response->assertOk()->assertJsonCount(2, 'data');

        $names = collect($response->json('data'))->pluck('name')->all();

        $this->assertContains('Фонарь туристический', $names);
        $this->assertContains('Рюкзак походный', $names);
    }

    public function test_public_can_show_single_product(): void
    {
        $product = Product::factory()->for(Category::factory())->create();

        $response = $this->getJson('/api/products/'.$product->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.category.id', $product->category_id);
    }

    public function test_guest_cannot_manage_products(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $payload = [
            'name' => 'Тестовый товар',
            'description' => 'Описание',
            'price' => 999.99,
            'category_id' => $category->id,
        ];

        $this->postJson('/api/products', $payload)->assertUnauthorized();
        $this->putJson('/api/products/'.$product->id, $payload)->assertUnauthorized();
        $this->deleteJson('/api/products/'.$product->id)->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_update_and_delete_product(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $category = Category::factory()->create();

        $createPayload = [
            'name' => 'Новый товар',
            'description' => 'Описание товара',
            'price' => 1499.5,
            'category_id' => $category->id,
        ];

        $createResponse = $this->postJson('/api/products', $createPayload);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'Новый товар');

        $productId = $createResponse->json('data.id');

        $updatePayload = [
            'name' => 'Обновленный товар',
            'description' => 'Обновленное описание',
            'price' => 1999.99,
            'category_id' => $category->id,
        ];

        $this->putJson('/api/products/'.$productId, $updatePayload)
            ->assertOk()
            ->assertJsonPath('data.name', 'Обновленный товар');

        $this->deleteJson('/api/products/'.$productId)
            ->assertNoContent();

        $this->assertSoftDeleted('products', ['id' => $productId]);
    }

    public function test_product_validation_works_for_create_and_update(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $invalidPayload = [
            'name' => '',
            'description' => 'Описание',
            'price' => 0,
            'category_id' => 999999,
        ];

        $this->postJson('/api/products', $invalidPayload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'category_id']);

        $this->putJson('/api/products/'.$product->id, $invalidPayload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'category_id']);
    }
}
