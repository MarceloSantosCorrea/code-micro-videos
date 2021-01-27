<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TraitSaves;
use Tests\Traits\TraitValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, WithFaker, TraitValidations, TraitSaves;

    private Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = Genre::factory()->create();
    }

    public function test_index(): void
    {
        $response = $this->get(route('api.genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);
    }

    public function test_show(): void
    {
        $response = $this->get(route('api.genres.show', ['genre' => $this->genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());
    }

    public function test_invalidation_data(): void
    {
        $data = [
            'name'          => '',
            'categories_id' => '',
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256),
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'is_active' => 'a',
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');

        $data = [
            'categories_id' => [100],
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = Category::factory()->create();
        $category->delete();

        $data = [
            'categories_id' => [$category->id],
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    /**
     * @throws Exception
     */
    public function test_create(): void
    {
        $category_id = Category::factory()->create()->id;
        $name = $this->faker->colorName;
        $data = [
            'name' => $name,
        ];

        $response = $this->assertStore($data + ['categories_id' => [$category_id]], $data + ['is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $this->assertHasCategory($response->json('id'), $category_id);

        $data = [
            'name'      => $name,
            'is_active' => false,
        ];
        $this->assertStore($data + ['categories_id' => [$category_id]], $data + ['is_active' => false]);
    }

    /**
     * @throws Exception
     */
    public function test_update(): void
    {
        $category_id = Category::factory()->create()->id;

        $name = $this->faker->name;
        $data = [
            'name'      => $name,
            'is_active' => true,
        ];
        $response = $this->assertUpdate($data + ['categories_id' => [$category_id]], $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $this->assertHasCategory($response->json('id'), $category_id);
    }

    protected function assertHasCategory(string $genreId, string $categoryId)
    {
        $this->assertDatabaseHas('category_genre', [
            'genre_id'    => $genreId,
            'category_id' => $categoryId,
        ]);
    }

    public function test_sync_categories()
    {
        $categoriesId = Category::factory(3)->create()->pluck('id')->toArray();

        $sendData = [
            'name'          => $this->faker->name,
            'categories_id' => [$categoriesId[0]],
        ];

        $response = $this->json('post', $this->routeStore(), $sendData);
        $this->assertHasCategory($response->json('id'), $categoriesId[0]);

        $sendData = [
            'name'          => $this->faker->name,
            'categories_id' => [$categoriesId[1], $categoriesId[2]],
        ];

        $response = $this->json('put', route('api.genres.update', ['genre' => $response->json('id')]), $sendData);

        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id'    => $response->json('id'),
        ]);

        $this->assertHasCategory($response->json('id'), $categoriesId[1]);
        $this->assertHasCategory($response->json('id'), $categoriesId[2]);
    }

    public function test_rollback_store()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn(['name' => 'test']);

        $controller->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $request = \Mockery::mock(Request::class);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $hasErrors = false;
        try {
            $controller->store($request);
        } catch (TestException $exception) {
            $this->assertCount(1, Genre::all());
            $hasErrors = true;
        }

        $this->assertTrue($hasErrors);
    }

    public function test_rollback_update()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->genre);

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn(['name' => 'test']);

        $controller->shouldReceive('rulesUpdate')
            ->withAnyArgs()
            ->andReturn([]);

        $request = \Mockery::mock(Request::class);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $hasErrors = false;
        try {
            $controller->update($request, 1);
        } catch (TestException $exception) {
            $this->assertCount(1, Genre::all());
            $hasErrors = true;
        }

        $this->assertTrue($hasErrors);
    }

    public function test_destroy(): void
    {
        $response = $this->json('delete', route('api.genres.destroy', ['genre' => $this->genre->id]));
        $response->assertStatus(204);

        $this->assertNull(Genre::find($this->genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($this->genre->id));
    }

    protected function model(): string
    {
        return Genre::class;
    }

    protected function routeStore(): string
    {
        return route('api.genres.store');
    }

    protected function routeUpdate(): string
    {
        return route('api.genres.update', ['genre' => $this->genre->id]);
    }
}
