<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\AbstractController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;

class AbstractControllerTest extends TestCase
{
    use WithFaker;

    private CategoryControllerStub $controller;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();

        $this->controller = new CategoryControllerStub();
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function test_index()
    {
        $category = CategoryStub::create([
            'name'        => $this->faker->colorName,
            'description' => $this->faker->sentence,
        ]);

        $resource = $this->controller->index();
        $serialized = $resource->response()->getData(true);

        $this->assertEquals([$category->toArray()], $serialized['data']);
        $this->assertArrayHasKey('meta', $serialized);
        $this->assertArrayHasKey('links', $serialized);
    }

    public function test_invalidation_data_in_store()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);

        $this->controller->store($request);
    }

    public function test_store()
    {
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn([
                'name'        => $this->faker->colorName,
                'description' => $this->faker->sentence,
            ]);

        $resource = $this->controller->store($request);
        $serialized = $resource->response()->getData(true);
        $this->assertEquals(CategoryStub::first()->toArray(), $serialized['data']);
    }

    public function test_if_find_or_fail_fetch_model()
    {
        $category = CategoryStub::create([
            'name'        => $this->faker->colorName,
            'description' => $this->faker->sentence,
        ]);

        $reflectionClass = new \ReflectionClass(AbstractController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [$category->id]);
        $this->assertInstanceOf(CategoryStub::class, $result);
    }

    public function test_if_find_or_fail_throw_exception_when_id_invalid()
    {
        $this->expectException(ModelNotFoundException::class);

        $reflectionClass = new \ReflectionClass(AbstractController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $reflectionMethod->invokeArgs($this->controller, [0]);
    }

    public function test_show()
    {
        $category = CategoryStub::create([
            'name'        => $this->faker->colorName,
            'description' => $this->faker->sentence,
        ]);

        $resource = $this->controller->show($category->id);
        $serialized = $resource->response()->getData(true);
        $this->assertEquals($category->toArray(), $serialized['data']);
    }

    public function test_update()
    {
        $category = CategoryStub::create([
            'name'        => $this->faker->colorName,
            'description' => $this->faker->sentence,
        ]);

        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn([
                'name'        => $this->faker->colorName,
                'description' => $this->faker->sentence,
            ]);

        $resource = $this->controller->update($request, $category->id);
        $serialized = $resource->response()->getData(true);
        $category->refresh();
        $this->assertEquals($category->toArray(), $serialized['data']);
    }

    public function test_destroy()
    {
        $category = CategoryStub::create([
            'name'        => $this->faker->colorName,
            'description' => $this->faker->sentence,
        ]);

        $result = $this->controller->destroy($category->id);
        $this
            ->createTestResponse($result)
            ->assertStatus(204);

        $this->assertCount(0, CategoryStub::all());
    }
}
