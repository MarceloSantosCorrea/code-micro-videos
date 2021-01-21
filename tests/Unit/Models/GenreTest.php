<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use MarceloCorrea\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

class GenreTest extends TestCase
{
    private Genre $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Genre;
    }

    public function test_fillable_attributes()
    {
        $fillable = ['name', 'is_active'];
        $this->assertEqualsCanonicalizing($fillable, $this->model->getFillable());
    }

    public function test_if_use_traits()
    {
        $traits = [HasFactory::class, Uuid::class, SoftDeletes::class];
        $modelTraits = array_keys(class_uses(Genre::class));
        $this->assertEqualsCanonicalizing($traits, $modelTraits);
    }

    public function test_casts_attributes()
    {
        $casts = ['is_active' => 'boolean', 'deleted_at' => 'datetime'];
        $this->assertEqualsCanonicalizing($casts, $this->model->getCasts());
    }

    public function test_incrementing_attribute()
    {
        $this->assertFalse($this->model->getIncrementing());
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
