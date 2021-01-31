<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use MarceloCorrea\Uuid\Uuid;
use Throwable;

class Video extends Model
{
    use HasFactory, Uuid, SoftDeletes, UploadFiles;

    public $incrementing = false;
    protected $keyType = 'string';

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    public static $fieldFields = ['filme', 'banner', 'trailer'];

    protected $fillable = [
        'title', 'description', 'year_launched', 'opened', 'rating', 'duration',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'opened'        => 'boolean',
        'year_launched' => 'integer',
        'duration'      => 'integer',
    ];

    /**
     * @param array $attributes
     *
     * @return Builder|Model
     * @throws Throwable
     */
    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();

            /** @var Video $obj */
            $obj = static::query()->create($attributes);
            self::handleRelations($obj, $attributes);

            $obj->uploadFiles($files);
            // excluir arquivos antigos

            \DB::commit();

            return $obj;
        } catch (\Exception $e) {

            if (isset($obj)) {
                // excluir arquivos de uploads
            }
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param array $attributes
     * @param array $options
     *
     * @return bool
     * @throws Throwable
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            self::handleRelations($this, $attributes);
            if ($saved) {

            }
            \DB::commit();

            return $saved;
        } catch (\Exception $e) {

            // excluir arquivos de uploads

            \DB::rollBack();
            throw $e;
        }
    }

    public static function handleRelations(Video $video, array $attributes = [])
    {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }

        if (isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    protected function uploadDir(): string
    {
        return $this->id;
    }
}
