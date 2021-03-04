<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\AbstractController;
use App\Http\Resources\CategoryResource;
use Tests\Stubs\Models\CategoryStub;

class CategoryControllerStub extends AbstractController
{
    protected function model(): string
    {
        return CategoryStub::class;
    }

    protected function rulesStore(): array
    {
        return [
            'name'        => 'required|max:255',
            'description' => 'nullable',
            'is_active'   => 'boolean',
        ];
    }

    protected function rulesUpdate(): array
    {
        return [
            'name'        => 'required|max:255',
            'description' => 'nullable',
            'is_active'   => 'boolean',
        ];
    }

    protected function resource(): string
    {
        return CategoryResource::class;
    }

    protected function resourceCollection(): string
    {
        return CategoryResource::class;
    }
}
