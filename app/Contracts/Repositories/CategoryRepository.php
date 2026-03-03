<?php

namespace App\Contracts\Repositories;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepository {
    public function all() : Collection;
    public function store(Request $request) : Category;
    public function delete(Category $category) : void;
    public function findById(int $categoryId) : Category;
    public function update(Category $category, Request $request) : void;
}
