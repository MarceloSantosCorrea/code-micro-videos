<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
use Illuminate\Http\Request;

class VideoController extends AbstractController
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            'title'         => 'required|max:255',
            'description'   => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened'        => 'boolean',
            'rating'        => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration'      => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genres_id'     => [
                'required',
                'array',
                'exists:genres,id,deleted_at,NULL',
            ],
            'thumb_file'    => 'image|max:' . Video::THUMB_FILE_MAX_SIZE,
            'banner_file'   => 'image|max:' . Video::BANNER_FILE_MAX_SIZE,
            'trailer_file'  => 'mimetypes:video/mp4|max:' . Video::TRAILER_FILE_MAX_SIZE,
            'video_file'    => 'mimetypes:video/mp4|max:' . Video::VIDEO_FILE_MAX_SIZE,
        ];
    }

    protected function model(): string
    {
        return Video::class;
    }

    protected function rulesStore(): array
    {
        return $this->rules;
    }

    protected function rulesUpdate(): array
    {
        return $this->rules;
    }

    public function store(Request $request)
    {
        $this->addRuleIfGenreHasCategories($request);
        $validatedData = $this->validate($request, $this->rulesStore());
        $model = $this->model()::create($validatedData);
        $model->refresh();
        $resource = $this->resource();

        return new $resource($model);
    }

    public function update(Request $request, $id)
    {
        $model = $this->findOrFail($id);
        $this->addRuleIfGenreHasCategories($request);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $model->update($validatedData);
        $resource = $this->resource();

        return new $resource($model);
    }

    protected function addRuleIfGenreHasCategories(Request $request)
    {
        $categoriesId = $request->get('categories_id');
        $categoriesId = is_array($categoriesId) ? $categoriesId : [];
        $this->rules['genres_id'][] = new GenresHasCategoriesRule($categoriesId);
    }

    protected function resourceCollection(): string
    {
        return $this->resource();
    }

    protected function resource(): string
    {
        return VideoResource::class;
    }
}