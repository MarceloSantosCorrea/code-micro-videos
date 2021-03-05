<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MarceloCorrea\Uuid\Uuid;
use Throwable;

class Video extends Model
{
    use HasFactory, Uuid, SoftDeletes, UploadFiles;

    public $incrementing = false;
    protected $keyType = 'string';

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    const THUMB_FILE_MAX_SIZE = 1024 * 5;        // 5MB
    const BANNER_FILE_MAX_SIZE = 1024 * 10;      // 10MB
    const TRAILER_FILE_MAX_SIZE = 1024 * 1024;   // 1GB
    const VIDEO_FILE_MAX_SIZE = 1024 * 1024 * 5; // 50GB

    public static array $fileFields = ['thumb_file', 'banner_file', 'trailer_file', 'video_file'];

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'thumb_file',
        'banner_file',
        'trailer_file',
        'video_file',
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
            \DB::commit();
            return $obj;
        } catch (\Exception $e) {
            if (isset($obj)) {
                $obj->deleteFiles($files);
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
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            self::handleRelations($this, $attributes);
            if ($saved) {
                $this->uploadFiles($files);
            }
            \DB::commit();
            if ($saved && count($files)) {
                $this->deleteOldFiles();
            }
            return $saved;
        } catch (\Exception $e) {

            $this->deleteFiles($files);

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

    public function getThumbFileUrlAttribute(): ?string
    {
        return $this->thumb_file ? $this->getFileUrl($this->thumb_file) : null;
    }

    public function getBannerFileUrlAttribute(): ?string
    {
        return $this->banner_file ? $this->getFileUrl($this->banner_file) : null;
    }

    public function getTrailerFileUrlAttribute(): ?string
    {
        return $this->trailer_file ? $this->getFileUrl($this->trailer_file) : null;
    }

    public function getVideoFileUrlAttribute(): ?string
    {
        return $this->video_file ? $this->getFileUrl($this->video_file) : null;
    }
}
