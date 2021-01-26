<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\AbstractController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;

class AbstractControllerTest extends TestCase
{
    use WithFaker;

    private $controller;

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

        $result = $this->controller->index()->toArray();

        $this->assertEquals([$category->toArray()], $result);
    }

    public function test_invalidation_data_in_store()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn([
                'name' => '',
            ]);

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

        $model = $this->controller->store($request);
        $this->assertEquals(
            CategoryStub::find(1)->toArray(),
            $model->toArray()
        );
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
}
