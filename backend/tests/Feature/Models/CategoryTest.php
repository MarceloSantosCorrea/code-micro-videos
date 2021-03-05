<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    private Category $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Category;
    }

    public function test_list()
    {
        $this->model::factory()->create();

        $all = $this->model::all();
        $this->assertCount(1, $all);

        $attr = ['id', 'name', 'description', 'is_active', 'deleted_at', 'created_at', 'updated_at'];
        $modelKeys = array_keys($all->first()->getAttributes());

        $this->assertEqualsCanonicalizing($attr, $modelKeys);
    }

    public function test_create()
    {
        $name = $this->faker->colorName;

        $create = $this->model::create([
            'name' => $name,
        ])->refresh();

        $this->assertTrue(Uuid::isValid($create->id));
        $this->assertEquals($name, $create->name);
        $this->assertNull($create->description);
        $this->assertTrue($create->is_active);

        $create = $this->model::create([
            'name'        => $name,
            'description' => null,
        ])->refresh();

        $this->assertNull($create->description);

        $description = $this->faker->sentence;
        $create = $this->model::create([
            'name'        => $name,
            'description' => $description,
        ])->refresh();

        $this->assertEquals($description, $create->description);

        $create = $this->model::create([
            'name'      => $name,
            'is_active' => true,
        ])->refresh();

        $this->assertTrue($create->is_active);

        $create = $this->model::create([
            'name'      => $name,
            'is_active' => false,
        ])->refresh();

        $this->assertFalse($create->is_active);
    }

    public function test_update()
    {
        $model = $this->model::factory()->create([
            'description' => 'test_description',
        ]);

        $data = [
            'name'        => 'test_name_updated',
            'description' => 'test_description_updated',
            'is_active'   => false,
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
