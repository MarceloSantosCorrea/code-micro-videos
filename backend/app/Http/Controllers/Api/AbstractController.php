<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

abstract class AbstractController extends Controller
{
    protected int $paginationSize = 15;

    protected abstract function model(): string;

    protected abstract function rulesStore(): array;

    protected abstract function rulesUpdate(): array;

    protected abstract function resource(): string;

    protected abstract function resourceCollection(): string;

    /**
     * @return mixed
     * @throws \ReflectionException
     */
    public function index()
    {
        $data = !$this->paginationSize ? $this->model()::all() : $this->model()::paginate($this->paginationSize);
        $resourceCollectionClass = $this->resourceCollection();
        $refClass = new \ReflectionClass($resourceCollectionClass);
        return $refClass->isSubclassOf(ResourceCollection::class)
            ? new $resourceCollectionClass($data)
            : $resourceCollectionClass::collection($data);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $model = $this->model()::create($validatedData);
        $model->refresh();
        $resource = $this->resource();

        return new $resource($model);
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id)
    {
        $model = $this->findOrFail($id);
        $resource = $this->resource();
        return new $resource($model);
    }

    public function update(Request $request, $id)
    {
        $model = $this->findOrFail($id);

        $validatedData = $this->validate($request, $this->rulesUpdate());
        $model->update($validatedData);
        $resource = $this->resource();

        return new $resource($model);
    }

    public function destroy($id): Response
    {
        $model = $this->findOrFail($id);
        $model->delete();

        return response()->noContent();
    }
}
