<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\TraitSaves;
use Tests\Traits\TraitValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, WithFaker, TraitValidations, TraitSaves;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
    }

    public function test_index(): void
    {
        $response = $this->get(route('api.categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
    }

    public function test_show(): void
    {
        $response = $this->get(route('api.categories.show', ['category' => $this->category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->category->toArray());
    }

    public function test_invalidation_data(): void
    {
        $this->assertInvalidationInStoreAction(['name' => ''], 'required');
        $this->assertInvalidationInUpdateAction(['name' => ''], 'required');

        $this->assertInvalidationInStoreAction(['name' => str_repeat('a', 256)], 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction(['name' => str_repeat('a', 256)], 'max.string', ['max' => 255]);

        $this->assertInvalidationInStoreAction(['is_active' => 'a'], 'boolean');
        $this->assertInvalidationInUpdateAction(['is_active' => 'a'], 'boolean');
    }

    /**
     * @throws Exception
     */
    public function test_create(): void
    {
        $name = $this->faker->colorName;
        $data = ['name' => $name];

        $response = $this->assertStore($data, $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $description = $this->faker->sentence;
        $data = [
            'name'        => $name,
            'description' => $description,
            'is_active'   => false,
        ];
        $this->assertStore($data, $data + ['description' => $description, 'is_active' => false]);
    }

    /**
     * @throws Exception
     */
    public function test_update(): void
    {
        $this->category = Category::factory()->create([
            'description' => $this->faker->sentence,
            'is_active'   => false,
        ]);

        $name = $this->faker->name;
        $description = $this->faker->sentence;

        $data = [
            'name'        => $name,
            'description' => $description,
            'is_active'   => true,
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = [
            'name'        => $name,
            'description' => '',
        ];
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data = [
            'name'        => $name,
            'description' => null,
        ];
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data = [
            'name'        => $name,
            'description' => $description,
        ];
        $this->assertUpdate($data, array_merge($data, ['description' => $description]));
    }

    public function test_destroy(): void
    {
        $response = $this->json('delete', route('api.categories.destroy', ['category' => $this->category->id]));
        $response->assertStatus(204);

        $this->assertNull(Category::find($this->category->id));
        $this->assertNotNull(Category::withTrashed()->find($this->category->id));
    }

    protected function model(): string
    {
        return Category::class;
    }

    protected function routeStore(): string
    {
        return route('api.categories.store');
    }

    protected function routeUpdate(): string
    {
        return route('api.categories.update', ['category' => $this->category->id]);
    }
}
