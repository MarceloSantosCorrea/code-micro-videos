<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends AbstractController
{
    private array $rules = [
        'name'        => 'required|max:255',
        'description' => 'nullable',
        'is_active'   => 'boolean',
    ];

    public function index(): AnonymousResourceCollection
    {
        $collection = parent::index();

        return CategoryResource::collection($collection);
    }

    protected function model(): string
    {
        return Category::class;
    }

    protected function rulesStore(): array
    {
        return $this->rules;
    }

    protected function rulesUpdate(): array
    {
        return $this->rules;
    }

    protected function resource(): string
    {
        return CategoryResource::class;
    }
}
