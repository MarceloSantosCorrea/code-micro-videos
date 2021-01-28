<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MarceloCorrea\Uuid\Uuid;

class Video extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    protected $fillable = [
        'title', 'description', 'year_launched', 'opened', 'rating', 'duration',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'opened'        => 'boolean',
        'year_launched' => 'integer',
        'duration'      => 'integer',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }
}
