<?php

namespace Tests\Feature\Http\Controllers\Api;

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

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, WithFaker, TraitValidations, TraitSaves;

    private Video $video;
    private array $sendData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->video = Video::factory()->create([
            'opened' => false,
        ]);

        $this->sendData = [
            'title'         => 'title',
            'description'   => 'description',
            'year_launched' => 2010,
            'rating'        => Video::RATING_LIST[0],
            'duration'      => 90,
        ];
    }

    public function test_index(): void
    {
        $response = $this->get(route('api.videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function test_invalidation_reqired()
    {
        $data = [
            'title'         => '',
            'description'   => '',
            'year_launched' => '',
            'rating'        => '',
            'duration'      => '',
        ];

        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function test_invalidation_max()
    {
        $data = [
            'title' => str_repeat('a', 256),
        ];

        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function test_invalidation_integer()
    {
        $data = [
            'duration' => 's',
        ];

        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function test_invalidation_year_launched_field()
    {
        $data = [
            'year_launched' => 'a',
        ];

        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function test_invalidation_boolean()
    {
        $data = [
            'opened' => 'true',
        ];

        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function test_invalidation_rating_in()
    {
        $data = [
            'rating' => 0,
        ];

        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function test_invalidation_categories_id_field()
    {
        $data = [
            'categories_id' => 'a',
        ];

        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

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

    public function test_invalidation_genres_id_field()
    {
        $data = [
            'genres_id' => 'a',
        ];

        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'genres_id' => [100],
        ];

        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $genre = Genre::factory()->create();
        $genre->delete();

        $data = [
            'genres_id' => [$genre->id],
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    /**
     * @throws Exception
     */
    public function test_save()
    {
        $category = Category::factory()->create();
        $genre = Genre::factory()->create();
        $genre->categories()->sync($category->id);

        $sendData = $this->sendData + ['categories_id' => [$category->id], 'genres_id' => [$genre->id]];

        $data = [
            [
                'send_data' => $sendData,
                'test_data' => $this->sendData + ['opened' => false],
            ],
            [
                'send_data' => $sendData + ['opened' => true],
                'test_data' => $this->sendData + ['opened' => true],
            ],
            [
                'send_data' => $sendData + ['rating' => Video::RATING_LIST[1]],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]],
            ],
        ];

        foreach ($data as $key => $value) {

            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($response->json('id'), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($response->json('id'), $value['send_data']['genres_id'][0]);

            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($response->json('id'), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($response->json('id'), $value['send_data']['genres_id'][0]);
        }
    }

    public function test_async_categories()
    {
        $categoriesId = Category::factory(3)->create()->pluck('id')->toArray();
        $genre = Genre::factory()->create();
        $genre->categories()->sync($categoriesId);
        $genreId = $genre->id;

        $sendData = $this->sendData + ['genres_id' => [$genreId], 'categories_id' => [$categoriesId[0]]];
        $response = $this->json('post', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[0], 'video_id' => $response->json('id'),
        ]);

        $sendData = $this->sendData + [
                'genres_id'     => [$genreId],
                'categories_id' => [$categoriesId[1], $categoriesId[2]],
            ];
        $response = $this->json(
            'put',
            route('api.videos.update', ['video' => $response->json('id')]),
            $sendData
        );

        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0], 'video_id' => $response->json('id'),
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1], 'video_id' => $response->json('id'),
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[2], 'video_id' => $response->json('id'),
        ]);
    }

    public function test_async_genres()
    {
        $genres = Genre::factory(3)->create();
        $genresId = $genres->pluck('id')->toArray();
        $categoryId = Category::factory()->create()->id;
        $genres->each(function ($genre) use ($categoryId) {
            $genre->categories()->sync($categoryId);
        });

        $sendData = $this->sendData + ['genres_id' => [$genresId[0]], 'categories_id' => [$categoryId]];
        $response = $this->json('post', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[0], 'video_id' => $response->json('id'),
        ]);

        $sendData = $this->sendData + ['genres_id' => [$genresId[1], $genresId[2]], 'categories_id' => [$categoryId]];
        $response = $this->json(
            'put',
            route('api.videos.update', ['video' => $response->json('id')]),
            $sendData
        );

        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresId[0], 'video_id' => $response->json('id'),
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[1], 'video_id' => $response->json('id'),
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[2], 'video_id' => $response->json('id'),
        ]);
    }

    public function test_rollback_store()
    {
        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->withAnyArgs()
            ->andReturnNull();

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $hasErrors = false;
        try {
            $controller->store($request);
        } catch (TestException $exception) {
            $this->assertCount(1, Video::all());
            $hasErrors = true;
        }
        $this->assertTrue($hasErrors);
    }

    public function test_rollback_update()
    {
        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->video);

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn(['name' => 'test']);

        $controller->shouldReceive('rulesUpdate')
            ->withAnyArgs()
            ->andReturn([]);

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->withAnyArgs()
            ->andReturnNull();

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $hasErrors = false;
        try {
            $controller->update($request, 1);
        } catch (TestException $exception) {
            $this->assertCount(1, Video::all());
            $hasErrors = true;
        }

        $this->assertTrue($hasErrors);
    }

    public function test_show(): void
    {
        $response = $this->get(route('api.videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function test_destroy(): void
    {
        $response = $this->json('delete', route('api.videos.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204);

        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    protected function assertHasCategory(string $videoId, string $categoryId)
    {
        $this->assertDatabaseHas('category_video', [
            'video_id'    => $videoId,
            'category_id' => $categoryId,
        ]);
    }

    protected function assertHasGenre(string $videoId, string $genreId)
    {
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $videoId,
            'genre_id' => $genreId,
        ]);
    }

    protected function model(): string
    {
        return Video::class;
    }

    protected function routeStore(): string
    {
        return route('api.videos.store');
    }

    protected function routeUpdate(): string
    {
        return route('api.videos.update', ['video' => $this->video->id]);
    }
}
