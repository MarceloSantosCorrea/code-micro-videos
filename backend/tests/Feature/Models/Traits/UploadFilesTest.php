<?php

namespace Tests\Feature\Models\Traits;

use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;

class UploadFilesTest extends TestCase
{
    private $obj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new UploadFilesStub();
        UploadFilesStub::dropTable();
        UploadFilesStub::makeTable();
    }

    public function test_make_old_fields_on_saving()
    {
        $this->obj->fill([
            'name'  => 'test',
            'file1' => 'test1.mp4',
            'file2' => 'test2.mp4',
        ]);

        $this->obj->save();
        $this->assertCount(0, $this->obj->oldFiles);

        $this->obj->update([
            'name'  => 'test_name',
            'file2' => 'test3.mp4',
        ]);

        $this->assertEqualsCanonicalizing(['test2.mp4'], $this->obj->oldFiles);
    }

    public function test_make_old_files_null_on_saving()
    {
        $this->obj->fill([
            'name' => 'test',
        ]);

        $this->obj->save();
        $this->obj->update([
            'name'  => 'test_name',
            'file2' => 'test3.mp4',
        ]);

        $this->assertEqualsCanonicalizing([], $this->obj->oldFiles);
    }
}
