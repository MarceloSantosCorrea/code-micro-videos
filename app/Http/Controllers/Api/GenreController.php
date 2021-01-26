<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GenreController extends Controller
{
    private $rules = [
        'name'      => 'required|max:255',
        'is_active' => 'boolean',
    ];

    public function index()
    {
        return Genre::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->rules);

        $model = Genre::create($request->all());
        $model->refresh();

        return $model;
    }

    public function show(Genre $genre): Genre
    {
        return $genre;
    }

    public function update(Request $request, Genre $genre): Genre
    {
        $this->validate($request, $this->rules);

        $genre->update($request->all());

        return $genre;
    }

    public function destroy(Genre $genre): Response
    {
        $genre->delete();

        return response()->noContent();
    }
}
