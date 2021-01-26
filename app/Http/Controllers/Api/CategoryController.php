<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends AbstractController
{
    protected function model(): string
    {
        return Category::class;
    }

    private $rules = [
        'name'      => 'required|max:255',
        'is_active' => 'boolean',
    ];

    public function store(Request $request)
    {
        $this->validate($request, $this->rules);

        $model = Category::create($request->all());
        $model->refresh();

        return $model;
    }

    public function show(Category $category): Category
    {
        return $category;
    }

    public function update(Request $request, Category $category): Category
    {
        $this->validate($request, $this->rules);

        $category->update($request->all());

        return $category;
    }

    public function destroy(Category $category): Response
    {
        $category->delete();

        return response()->noContent();
    }
}
