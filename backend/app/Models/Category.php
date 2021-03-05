<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MarceloCorrea\Uuid\Uuid;

class Category extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name', 'description', 'is_active'];
    protected $dates = ['deleted_at'];
    protected $casts = ['is_active' => 'boolean'];
}
