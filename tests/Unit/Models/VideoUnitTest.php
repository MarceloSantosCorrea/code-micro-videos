<?php

namespace Tests\Unit\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use MarceloCorrea\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

class VideoUnitTest extends TestCase
{
    private Video $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Video;
    }

    public function test_fillable_attributes()
    {
        $fillable = [
            'title', 'description', 'year_launched', 'opened', 'rating', 'duration',
        ];
        $this->assertEqualsCanonicalizing($fillable, $this->model->getFillable());
    }

    public function test_if_use_traits()
    {
        $traits = [HasFactory::class, Uuid::class, SoftDeletes::class, UploadFiles::class];
        $modelTraits = array_keys(class_uses(Video::class));
        $this->assertEquals($traits, $modelTraits);
    }

    public function test_casts_attributes()
    {
        $casts = [
            'deleted_at'    => 'datetime',
            'opened'        => 'boolean',
            'year_launched' => 'integer',
            'duration'      => 'integer',
        ];
        $this->assertEqualsCanonicalizing($casts, $this->model->getCasts());
    }

    public function test_incrementing_attribute()
    {
        $model = new Video();
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
