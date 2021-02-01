<?php

namespace Tests\Stubs\Models;

use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class UploadFilesStub extends Model
{
    use UploadFiles;

//    public static $fieldFields = ['filme', 'banner', 'trailer'];
    public static $fieldFields = ['file1', 'file2'];

//    protected $table = 'upload_file_stubs';
//    protected $fillable = ['name', 'file1', 'file2'];
//    public static $fileFields = ['file1', 'file2'];
//
//    public static function makeTable()
//    {
//        \Schema::create('upload_file_stubs', function (Blueprint $table) {
//            $table->bigIncrements('id');
//            $table->string('name');
//            $table->string('file1');
//            $table->string('file2');
//            $table->timestamps();
//        });
//    }
//
//    public static function dropTable()
//    {
//        \Schema::dropIfExists('upload_file_stubs');
//    }

    protected function uploadDir(): string
    {
        return "1";
    }
}
