<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, WithFaker, TestValidations, TestSaves;

    private string $modelName = CastMember::class;
    private CastMember $model;
    private CastMember $instanceModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new $this->modelName;
        $this->instanceModel = $this->model::factory()->create([
            'type' => CastMember::TYPE_DIRECTOR,
        ]);
    }

    public function test_index(): void
    {
        $response = $this->get(route('api.cast_members.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->instanceModel->toArray()]);
    }

    public function test_show(): void
    {
        $response = $this->get(route('api.cast_members.show', ['cast_member' => $this->instanceModel->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->instanceModel->toArray());
    }

    public function test_invalidation_data(): void
    {
        $data = ['name' => '', 'type' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['type' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    /**
     * @throws Exception
     */
    public function test_create(): void
    {
        $data = [
            ['name' => $this->faker->colorName, 'type' => CastMember::TYPE_DIRECTOR],
            ['name' => $this->faker->colorName, 'type' => CastMember::TYPE_ACTOR],
        ];

        foreach ($data as $value) {
            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
        }
    }

    /**
     * @throws Exception
     */
    public function test_update(): void
    {
        $data = [
            'name' => $this->faker->name,
            'type' => CastMember::TYPE_ACTOR,
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function test_destroy(): void
    {
        $response = $this->json('delete', route('api.cast_members.destroy', ['cast_member' => $this->instanceModel->id]));
        $response->assertStatus(204);

        $this->assertNull($this->model->find($this->instanceModel->id));
        $this->assertNotNull($this->model::withTrashed()->find($this->instanceModel->id));
    }

    protected function model(): string
    {
        return $this->modelName;
    }

    protected function routeStore(): string
    {
        return route('api.cast_members.store');
    }

    protected function routeUpdate(): string
    {
        return route('api.cast_members.update', ['cast_member' => $this->instanceModel->id]);
    }
}
