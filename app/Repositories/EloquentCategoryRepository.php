<?php

namespace App\Repositories;

use App\Contracts\CategoryRepository;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class EloquentCategoryRepository implements CategoryRepository {

    public function all() : Collection
    {
        return Category::all();
    }

    public function store(Request $request) : Category
    {
        return Category::create($request->all());
    }

    public function delete(Category $category) : void
    {
        $category->delete();
    }

    public function findById(int $categoryId): Category
    {
        return Category::find($categoryId);
    }

    public function update(Category $category, Request $request) : void
    {
        $category->update($request->all());
    }

    public function orderBy(string $coluna): Collection
    {
        return Category::orderBy($coluna)->get();
    }
}
