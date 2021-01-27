<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
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
            'genres_id'     => 'required|array|exists:genres,id,deleted_at,NULL',
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
        $validatedData = $this->validate($request, $this->rulesStore());

        $self = $this;
        $model = \DB::transaction(function () use ($request, $self, $validatedData) {

            $model = $this->model()::create($validatedData);
            $self->handleRelations($request, $model);

            return $model;
        });
        $model->refresh();

        return $model;
    }

    public function update(Request $request, $id)
    {
        $model = $this->findOrFail($id);

        $validatedData = $this->validate($request, $this->rulesUpdate());

        $self = $this;
        $model = \DB::transaction(function () use ($request, $self, $model, $validatedData) {

            $model->update($validatedData);
            $self->handleRelations($request, $model);

            return $model;
        });

        return $model;
    }

    protected function handleRelations(Request $request, Video $video)
    {
        $video->categories()->sync($request->get('categories_id'));
        $video->genres()->sync($request->get('genres_id'));
    }
}
