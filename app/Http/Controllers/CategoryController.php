<?php

namespace App\Http\Controllers;

use App\Contracts\CategoryRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\CategoryRequest;
use Inertia\Inertia;

class CategoryController extends Controller
{

    public function __construct(
        private readonly CategoryRepository $categoryRepository
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = $this->categoryRepository->all();

        return Inertia::render('Categories/Index', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        try {
            $this->categoryRepository->store($request);
            return redirect()->back();

        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, $categoryId)
    {
        try {
            $category = $this->categoryRepository->findById($categoryId);
            $this->categoryRepository->update($category, $request);
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($categoryId)
    {
        try {
            $category = $this->categoryRepository->findById($categoryId);
            $this->categoryRepository->delete($category);
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
