<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use MarceloCorrea\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private Category $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Category;
    }

    public function test_fillable_attributes()
    {
        $fillable = ['name', 'description', 'is_active'];
        $this->assertEqualsCanonicalizing($fillable, $this->model->getFillable());
    }

    public function test_if_use_traits()
    {
        $traits = [HasFactory::class, Uuid::class, SoftDeletes::class];
        $modelTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $modelTraits);
    }

    public function test_casts_attributes()
    {
        $casts = ['is_active' => 'boolean', 'deleted_at' => 'datetime'];
        $this->assertEqualsCanonicalizing($casts, $this->model->getCasts());
    }

    public function test_incrementing_attribute()
    {
        $model = new Category();
        $this->assertFalse($model->getIncrementing());
    }

    public function test_key_type_attribute()
    {
        $this->assertEquals('string', $this->model->getKeyType());
    }

    public function test_dates_attributes()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        $this->assertEqualsCanonicalizing($dates, array_values($this->model->getDates()));
    }
}
