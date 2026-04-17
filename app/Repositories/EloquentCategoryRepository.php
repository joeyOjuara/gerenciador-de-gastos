<?php

namespace App\Repositories;

use App\Contracts\CategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class EloquentCategoryRepository implements CategoryRepository {

    public function all() : Collection
    {
        return Category::where('user_id', Auth::id())->get();
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
        return Category::where('user_id', Auth::id())->where('id', $categoryId)->firstOrFail();
    }

    public function update(Category $category, Request $request) : void
    {
        $category->update($request->only(['name']));
    }

    public function orderBy(string $coluna): Collection
    {
        return Category::where('user_id', Auth::id())->orderBy($coluna)->get();
    }
}
