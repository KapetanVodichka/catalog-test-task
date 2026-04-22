<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_get_categories_list_without_pagination(): void
    {
        Category::factory()->count(4)->create();

        $response = $this->getJson('/api/categories');

        $response
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure([
                'data' => [
                    ['id', 'name', 'description', 'created_at', 'updated_at'],
                ],
            ])
            ->assertJsonMissingPath('links')
            ->assertJsonMissingPath('meta');
    }
}
