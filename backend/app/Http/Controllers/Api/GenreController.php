<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends AbstractController
{
    private array $rules = [
        'name'          => 'required|max:255',
        'is_active'     => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
    ];

    protected function model(): string
    {
        return Genre::class;
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
        $resource = $this->resource();

        return new $resource($model);
    }

    public function update(Request $request, $id)
    {
        $model = $this->findOrFail($id);

        $validatedData = $this->validate($request, $this->rulesUpdate());

        $self = $this;
        \DB::transaction(function () use ($request, $self, $model, $validatedData) {
            $model->update($validatedData);
            $self->handleRelations($request, $model);
        });

        $resource = $this->resource();

        return new $resource($model);
    }

    protected function handleRelations(Request $request, Genre $genre)
    {
        $genre->categories()->sync($request->get('categories_id'));
    }

    protected function resourceCollection(): string
    {
        return $this->resource();
    }

    protected function resource(): string
    {
        return GenreResource::class;
    }
}
