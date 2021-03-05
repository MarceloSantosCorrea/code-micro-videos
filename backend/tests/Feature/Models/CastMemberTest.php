<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    private CastMember $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CastMember;
    }

    public function test_list()
    {
        $this->model::factory()->create();

        $all = $this->model::all();
        $this->assertCount(1, $all);

        $attr = ['id', 'name', 'type', 'deleted_at', 'created_at', 'updated_at'];
        $modelKeys = array_keys($all->first()->getAttributes());

        $this->assertEqualsCanonicalizing($attr, $modelKeys);
    }

    public function test_create()
    {
        $name = $this->faker->colorName;
        $create = $this->model::create([
            'name' => $name,
            'type' => CastMember::TYPE_DIRECTOR,
        ])->refresh();

        $this->assertTrue(Uuid::isValid($create->id));
        $this->assertEquals($name, $create->name);
        $this->assertEquals(CastMember::TYPE_DIRECTOR, $create->type);
    }

    public function test_update()
    {
        $model = $this->model::factory()->create([
            'type' => CastMember::TYPE_DIRECTOR,
        ]);

        $data = [
            'name' => 'test_name_updated',
            'type' => CastMember::TYPE_ACTOR,
        ];

        $model->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $model->{$key});
        }
    }

    public function test_delete()
    {
        $model = $this->model::factory()->create();
        $model->delete();

        $all = $this->model::all();
        $this->assertCount(0, $all);
    }
}
