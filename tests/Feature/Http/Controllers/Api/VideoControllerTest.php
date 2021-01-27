<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Video;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\TraitSaves;
use Tests\Traits\TraitValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, WithFaker, TraitValidations, TraitSaves;

    private string $modelName = Video::class;
    private Video $model;
    private Video $instanceModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new $this->modelName;
        $this->instanceModel = $this->model::factory()->create();
    }

    public function test_index(): void
    {
        $response = $this->get(route('api.videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->instanceModel->toArray()]);
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

    public function test_show(): void
    {
        $response = $this->get(route('api.videos.show', ['video' => $this->instanceModel->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->instanceModel->toArray());
    }

//    public function test_invalidation_data(): void
//    {
//        $data = ['name' => '', 'type' => ''];
//        $this->assertInvalidationInStoreAction($data, 'required');
//        $this->assertInvalidationInUpdateAction($data, 'required');
//
//        $data = ['name' => str_repeat('a', 256)];
//        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
//        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
//
//        $data = ['type' => 'a'];
//        $this->assertInvalidationInStoreAction($data, 'in');
//        $this->assertInvalidationInUpdateAction($data, 'in');
//    }
//
//
//    /**
//     * @throws Exception
//     */
//    public function test_create(): void
//    {
//        $data = [
//            ['name' => $this->faker->colorName, 'type' => Video::TYPE_DIRECTOR],
//            ['name' => $this->faker->colorName, 'type' => Video::TYPE_ACTOR],
//        ];
//
//        foreach ($data as $value) {
//            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
//            $response->assertJsonStructure(['created_at', 'updated_at']);
//        }
//    }
//
//    /**
//     * @throws Exception
//     */
//    public function test_update(): void
//    {
//        $data = [
//            'name' => $this->faker->name,
//            'type' => Video::TYPE_ACTOR,
//        ];
//        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
//        $response->assertJsonStructure(['created_at', 'updated_at']);
//    }
//
//    public function test_destroy(): void
//    {
//        $response = $this->json('delete', route('api.videos.destroy', ['video' => $this->instanceModel->id]));
//        $response->assertStatus(204);
//
//        $this->assertNull($this->model->find($this->instanceModel->id));
//        $this->assertNotNull($this->model::withTrashed()->find($this->instanceModel->id));
//    }
//
    protected function model(): string
    {
        return $this->modelName;
    }

    protected function routeStore(): string
    {
        return route('api.videos.store');
    }

    protected function routeUpdate(): string
    {
        return route('api.videos.update', ['video' => $this->instanceModel->id]);
    }
}
