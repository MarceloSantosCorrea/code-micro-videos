<?php

namespace Tests\Feature\Models\Video;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\QueryException;

class VideoCrudTest extends BaseVideoTestCase
{
    private array $filefields = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (Video::$fileFields as $field) {
            $this->filefields[$field] = "$field.test";
        }
    }

    public function test_list()
    {
        Video::factory()->create();
        $videos = Video::all();
        $this->assertCount(1, $videos);

        $videoKeys = array_keys($videos->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id',
            'title',
            'description',
            'year_launched',
            'duration',
            'opened',
            'rating',
            'created_at',
            'updated_at',
            'thumb_file',
            'banner_file',
            'trailer_file',
            'video_file',
            'deleted_at',
        ], $videoKeys);
    }

    public function test_create_with_basic_fields()
    {
        $video = Video::create($this->data + $this->filefields);
        $video->refresh();

        $this->assertEquals(36, strlen($video->id));
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + $this->filefields + ['opened' => false]);

        $video = Video::create($this->data + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);
    }

    public function test_create_with_relations()
    {
        $category = Category::factory()->create();
        $genre = Genre::factory()->create();
        $video = Video::create(
            $this->data + [
                'categories_id' => $category->id,
                'genres_id'     => $genre->id,
            ]
        );

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function test_rollback_store()
    {
        $hasErrors = false;
        try {
            Video::create([
                'title'         => 'title',
                'description'   => 'description',
                'year_launched' => 2010,
                'rating'        => Video::RATING_LIST[0],
                'duration'      => 90,
                'categories_id' => [0, 1, 2],
            ]);
        } catch (QueryException $e) {
            $this->assertCount(0, Video::all());
            $hasErrors = true;
        }

        $this->assertTrue($hasErrors);
    }

    public function test_update_with_basic_fields()
    {
        $video = Video::factory()->create(['opened' => false]);
        $video->update($this->data + $this->filefields);

        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = Video::factory()->create(['opened' => false]);
        $video->update($this->data + $this->filefields + ['opened' => true]);

        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);
    }

    public function test_update_with_relations()
    {
        $category = Category::factory()->create();
        $genre = Genre::factory()->create();
        $video = Video::factory()->create();

        $video->update($this->data + [
                'categories_id' => $category->id,
                'genres_id'     => $genre->id,
            ]
        );

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function test_rollback_update()
    {
        $hasErrors = false;
        $video = Video::factory()->create();
        $oldTitle = $video->title;
        try {
            $video->update([
                'title'         => 'title',
                'description'   => 'description',
                'year_launched' => 2010,
                'rating'        => Video::RATING_LIST[0],
                'duration'      => 90,
                'categories_id' => [0, 1, 2],
            ]);
        } catch (QueryException $e) {
            $this->assertDatabaseHas('videos', [
                'title' => $oldTitle,
            ]);
            $hasErrors = true;
        }

        $this->assertTrue($hasErrors);
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

    public function test_handle_relations()
    {
        $video = Video::factory()->create();
        Video::handleRelations($video, []);
        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);

        $category = Category::factory()->create();
        Video::handleRelations($video, [
            'categories_id' => [$category->id],
        ]);
        $video->refresh();

        $this->assertCount(1, $video->categories);

        $genre = Genre::factory()->create();
        Video::handleRelations($video, [
            'genres_id' => [$genre->id],
        ]);
        $video->refresh();

        $this->assertCount(1, $video->genres);

        $video->categories()->detach($category->id);
        $video->genres()->detach($genre->id);
        $video->refresh();

        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);

        Video::handleRelations($video, [
            'categories_id' => [$category->id],
            'genres_id'     => [$genre->id],
        ]);
        $video->refresh();

        $this->assertCount(1, $video->categories);
        $this->assertCount(1, $video->genres);
    }

    public function test_async_categories()
    {
        $categoriesId = Category::factory(3)->create()->pluck('id')->toArray();
        $video = Video::factory()->create();
        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[0]],
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[0],
            'video_id'    => $video->id,
        ]);

        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[1], $categoriesId[2]],
        ]);

        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0],
            'video_id'    => $video->id,
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1],
            'video_id'    => $video->id,
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[2],
            'video_id'    => $video->id,
        ]);
    }

    public function test_async_genres()
    {
        $genresId = Genre::factory(3)->create()->pluck('id')->toArray();
        $video = Video::factory()->create();
        Video::handleRelations($video, [
            'genres_id' => [$genresId[0]],
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $video->id,
        ]);

        Video::handleRelations($video, [
            'genres_id' => [$genresId[1], $genresId[2]],
        ]);

        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $video->id,
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[1],
            'video_id' => $video->id,
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[2],
            'video_id' => $video->id,
        ]);
    }
}
