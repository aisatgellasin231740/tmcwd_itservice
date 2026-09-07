<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('tickets')->orderBy('name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request)
    {
        Category::create([
            'name'      => $request->validated('name'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Category added.');
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $category->update([
            'name'      => $request->validated('name'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Category updated.');
    }
}
