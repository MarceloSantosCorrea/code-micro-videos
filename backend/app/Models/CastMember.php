<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MarceloCorrea\Uuid\Uuid;

class CastMember extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;

    protected $fillable = ['name', 'type'];
    protected $dates = ['deleted_at'];
    protected $casts = ['type' => 'integer'];

}
