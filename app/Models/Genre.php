<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use MarceloCorrea\Uuid\Uuid;

/**
 * App\Models\Genre
 *
 * @property string      $id
 * @property string      $name
 * @property int         $is_active
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Genre newModelQuery()
 * @method static Builder|Genre newQuery()
 * @method static \Illuminate\Database\Query\Builder|Genre onlyTrashed()
 * @method static Builder|Genre query()
 * @method static Builder|Genre whereCreatedAt($value)
 * @method static Builder|Genre whereDeletedAt($value)
 * @method static Builder|Genre whereId($value)
 * @method static Builder|Genre whereIsActive($value)
 * @method static Builder|Genre whereName($value)
 * @method static Builder|Genre whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Genre withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Genre withoutTrashed()
 * @mixin Eloquent
 */
class Genre extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    public bool $incrementing = false;

    protected string $keyType = 'string';
    protected array $fillable = ['name', 'is_active'];
    protected array $dates = ['deleted_at'];
    protected array $casts = ['is_active' => 'boolean'];
}
