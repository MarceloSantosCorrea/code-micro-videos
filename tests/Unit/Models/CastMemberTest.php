<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use MarceloCorrea\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

class CastMemberTest extends TestCase
{
    private CastMember $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CastMember;
    }

    public function test_fillable_attributes()
    {
        $fillable = ['name', 'type'];
        $this->assertEqualsCanonicalizing($fillable, $this->model->getFillable());
    }

    public function test_if_use_traits()
    {
        $traits = [HasFactory::class, Uuid::class, SoftDeletes::class];
        $modelTraits = array_keys(class_uses(CastMember::class));
        $this->assertEquals($traits, $modelTraits);
    }

    public function test_casts_attributes()
    {
        $casts = ['type' => 'integer', 'deleted_at' => 'datetime'];
        $this->assertEqualsCanonicalizing($casts, $this->model->getCasts());
    }

    public function test_incrementing_attribute()
    {
        $model = new CastMember();
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

    public function test_constants_attributes()
    {
        $constants = ['TYPE_DIRECTOR', 'TYPE_ACTOR', 'CREATED_AT', 'UPDATED_AT'];
        $reflectionClass = new \ReflectionClass($this->model);
        $this->assertEquals($constants, array_keys($reflectionClass->getConstants()));
    }

    public function test_constants_values()
    {
        $this->assertEquals(1, $this->model::TYPE_DIRECTOR);
        $this->assertEquals(2, $this->model::TYPE_ACTOR);
    }
}
