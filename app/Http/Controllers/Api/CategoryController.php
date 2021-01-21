<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    private $rules = [
        'name'      => 'required|max:255',
        'is_active' => 'boolean',
    ];

    public function index(): LengthAwarePaginator
    {
        return (new \App\Models\Category)->paginate();
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, $this->rules);
        } catch (ValidationException $e) {
        }

        return Category::create($request->all());
    }

    public function show(Category $category): Category
    {
        return $category;
    }

    public function update(Request $request, Category $category): Category
    {
        try {
            $this->validate($request, $this->rules);
        } catch (ValidationException $e) {
        }

        $category->update($request->all());

        return $category;
    }

    public function destroy(Category $category): Response
    {
        try {
            $category->delete();
        } catch (\Exception $e) {
        }

        return response()->noContent();
    }
}
