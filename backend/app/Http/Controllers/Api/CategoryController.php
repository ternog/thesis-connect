<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::active()
            ->withCount('theses')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        if (!$request->user()->canManageUsers()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category->loadCount('theses'));
    }

    public function update(Request $request, Category $category)
    {
        if (!$request->user()->canManageUsers()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($request->only(['name', 'description', 'is_active']));

        return response()->json($category);
    }

    public function destroy(Request $request, Category $category)
    {
        if (!$request->user()->canManageUsers()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($category->theses()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated theses.'
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}