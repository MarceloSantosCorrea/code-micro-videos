<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    private Genre $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new Genre;
    }

    public function test_index()
    {
        $model = $this->model::factory()->create();

        $response = $this->get(route('api.genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$model->toArray()]);
    }

    public function test_show()
    {
        $model = $this->model::factory()->create();

        $response = $this->get(route('api.genres.show', ['genre' => $model->id]));

        $response
            ->assertStatus(200)
            ->assertJson($model->toArray());
    }

    public function test_invalidation_data_create_no_data()
    {
        $response = $this->json('post', route('api.genres.store'), []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                __('validation.required', ['attribute' => 'name']),
            ]);
    }

    public function test_invalidation_data_create_invalid_data()
    {
        $response = $this->json('post', route('api.genres.store'), [
            'name'      => str_repeat('a', 256),
            'is_active' => 'a',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255]),
            ])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active']),
            ]);
    }

    public function test_invalidation_data_update_no_data()
    {
        $model = Genre::factory()->create();
        $response = $this->json('put', route('api.genres.update', ['genre' => $model->id]), []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                __('validation.required', ['attribute' => 'name']),
            ]);
    }

    public function test_invalidation_data_update_invalid_data()
    {
        $model = Genre::factory()->create();

        $response = $this->json('put', route('api.genres.update', ['genre' => $model->id]), [
            'name'      => str_repeat('a', 256),
            'is_active' => 'a',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255]),
            ])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active']),
            ]);
    }

    public function test_create()
    {
        $name = $this->faker->colorName;
        $response = $this->json('post', route('api.genres.store'), [
            'name' => $name,
        ]);

        $id = $response->json('id');
        $model = $this->model::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($model->toArray());

        $this->assertTrue($response->json('is_active'));

        $description = $this->faker->sentence;
        $response = $this->json('post', route('api.genres.store'), [
            'name'      => $name,
            'is_active' => false,
        ]);

        $response
            ->assertJsonFragment([
                'is_active' => false,
            ]);
    }

    public function test_update()
    {
        $model = $this->model::factory()->create([
            'is_active' => false,
        ]);

        $name = $this->faker->name;
        $response = $this->json('put', route('api.genres.update', ['genre' => $model->id]), [
            'name'      => $name,
            'is_active' => true,
        ]);

        $id = $response->json('id');
        $model = $this->model::find($id);

        $response
            ->assertStatus(200)
            ->assertJson($model->toArray())
            ->assertJsonFragment([
                'name'      => $name,
                'is_active' => true,
            ]);
    }

    public function test_destroy()
    {
        $model = $this->model::factory()->create();
        $response = $this->json('delete', route('api.genres.destroy', ['genre' => $model->id]));
        $response->assertStatus(204);
        $this->assertNull($this->model->find($model->id));
        $this->assertNotNull($this->model::withTrashed()->find($model->id));
    }
}
