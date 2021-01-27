<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MarceloCorrea\Uuid\Uuid;

class CastMember extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    public bool $incrementing = false;
    protected $keyType = 'string';

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;

    protected array $fillable = ['name', 'type'];
    protected array $dates = ['deleted_at'];
    protected array $casts = ['type' => 'integer'];

}
