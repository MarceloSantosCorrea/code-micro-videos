<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MarceloCorrea\Uuid\Uuid;

class Video extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    public bool $incrementing = false;
    protected $keyType = 'string';

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    protected array $fillable = [
        'title', 'description', 'year_launched', 'opened', 'rating', 'duration',
    ];

    protected array $dates = ['deleted_at'];

    protected array $casts = [
        'opened'        => 'boolean',
        'year_launched' => 'integer',
        'duration'      => 'integer',
    ];
}
