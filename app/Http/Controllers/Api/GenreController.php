<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class GenreController extends Controller
{
    private $rules = [
        'name'      => 'required|max:255',
        'is_active' => 'boolean',
    ];

    public function index(): LengthAwarePaginator
    {
        return (new \App\Models\Genre)->paginate();
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, $this->rules);
        } catch (ValidationException $e) {
        }

        return Genre::create($request->all());
    }

    public function show(Genre $genre): Genre
    {
        return $genre;
    }

    public function update(Request $request, Genre $genre): Genre
    {
        try {
            $this->validate($request, $this->rules);
        } catch (ValidationException $e) {
        }

        $genre->update($request->all());

        return $genre;
    }

    public function destroy(Genre $genre): Response
    {
        try {
            $genre->delete();
        } catch (\Exception $e) {
        }

        return response()->noContent();
    }
}
