<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestUploads;

    public function test_invalidation_thumb_field()
    {
        $this->assertInvalidationFile('thumb_file', 'jpg', Video::THUMB_FILE_MAX_SIZE, 'image');
    }

    public function test_invalidation_banner_field()
    {
        $this->assertInvalidationFile('banner_file', 'jpg', Video::BANNER_FILE_MAX_SIZE, 'image');
    }

    public function test_invalidation_trailer_field()
    {
        $this->assertInvalidationFile(
            'trailer_file', 'mp4', Video::TRAILER_FILE_MAX_SIZE, 'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function test_invalidation_video_field()
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            Video::VIDEO_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function test_store_with_files()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json('post', $this->routeStore(), $this->sendData + $files);

        $response->assertStatus(201);
        $this->assertFilesOnPersist($response, $files);
    }

    public function test_update_with_files()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json('put', $this->routeUpdate(), $this->sendData + $files);

        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, $files);

        $newFiles = [
            'thumb_file' => UploadedFile::fake()->create('thumb_file.jpg'),
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
        ];

        $response = $this->json('put', $this->routeUpdate(), $this->sendData + $newFiles);
        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, Arr::except($files, ['thumb_file', 'video_file']) + $newFiles);

        $id = $response->json('id') ?? $response->json('data.id');
        /** @var Video $video */
        $video = Video::find($id);
        \Storage::assertMissing($video->relativeFilePath($files['thumb_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['video_file']->hashName()));
    }

    protected function assertFilesOnPersist(TestResponse $response, array $files): void
    {
        $id = $response->json('id') ?? $response->json('data.id');
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }

    protected function getFiles(): array
    {
        return [
            'thumb_file'   => UploadedFile::fake()->create('thumb_file.jpg'),
            'banner_file'  => UploadedFile::fake()->create('banner_file.jpg'),
            'trailer_file' => UploadedFile::fake()->create('trailer_file.mp4'),
            'video_file'   => UploadedFile::fake()->create('video_file.mp4'),
        ];
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
