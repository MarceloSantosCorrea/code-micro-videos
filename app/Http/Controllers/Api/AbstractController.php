<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class AbstractController extends Controller
{
    protected abstract function model();

    protected abstract function rulesStore();

    protected abstract function rulesUpdate();

    public function index()
    {
        return $this->model()::all();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $model = $this->model()::create($validatedData);
        $model->refresh();

        return $model;
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id)
    {
        return $this->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $model = $this->findOrFail($id);

        $validatedData = $this->validate($request, $this->rulesUpdate());
        $model->update($validatedData);

        return $model;
    }

    public function destroy($id): Response
    {
        $model = $this->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
