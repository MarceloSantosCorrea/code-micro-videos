<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, WithFaker, TestValidations, TestSaves, TestResources;

    private CastMember $castMember;
    private array $serializedFields = [
        'id',
        'name',
        'type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = CastMember::factory()->create([
            'type' => CastMember::TYPE_DIRECTOR,
        ]);
    }

    public function test_index(): void
    {
        $response = $this->get(route('api.cast_members.index'));

        $response
            ->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'per_page' => 15,
                ],
            ])
            ->assertJsonStructure([
                'data'  => [
                    '*' => $this->serializedFields,
                ],
                'links' => [],
                'meta'  => [],
            ]);

        $resource = CastMemberResource::collection(collect([$this->castMember]));
        $this->assertResource($response, $resource);
    }

    public function test_show(): void
    {
        $response = $this->get(route('api.cast_members.show', ['cast_member' => $this->castMember->id]));

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->serializedFields]);

        $id = $response->json('data.id');
        $resource = new CastMemberResource(CastMember::find($id));
        $this->assertResource($response, $resource);
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
    public function test_store(): void
    {
        $data = [
            ['name' => $this->faker->colorName, 'type' => CastMember::TYPE_DIRECTOR],
            ['name' => $this->faker->colorName, 'type' => CastMember::TYPE_ACTOR],
        ];

        foreach ($data as $value) {
            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure(['data' => $this->serializedFields]);
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
        $response->assertJsonStructure(['data' => $this->serializedFields]);
    }

    public function test_destroy(): void
    {
        $response = $this->json('delete', route('api.cast_members.destroy', ['cast_member' => $this->castMember->id]));
        $response->assertStatus(204);

        $this->assertNull(CastMember::find($this->castMember->id));
        $this->assertNotNull(CastMember::withTrashed()->find($this->castMember->id));
    }

    protected function model(): string
    {
        return CastMember::class;
    }

    protected function routeStore(): string
    {
        return route('api.cast_members.store');
    }

    protected function routeUpdate(): string
    {
        return route('api.cast_members.update', ['cast_member' => $this->castMember->id]);
    }
}
