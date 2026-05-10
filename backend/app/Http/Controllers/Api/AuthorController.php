<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $query = Author::query();

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        $authors = $query->orderBy('thesis_count', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($authors);
    }

    public function store(Request $request)
    {
        $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'email' => 'nullable|email|max:255',
            'department' => 'nullable|string|max:255',
            'author_type' => 'required|in:student,faculty',
        ]);

        $author = Author::create($request->all());

        return response()->json($author, 201);
    }

    public function show(Author $author)
    {
        return response()->json($author->load('theses'));
    }

    public function update(Request $request, Author $author)
    {
        $request->validate([
            'last_name' => 'sometimes|required|string|max:255',
            'first_name' => 'sometimes|required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'email' => 'nullable|email|max:255',
            'department' => 'nullable|string|max:255',
            'author_type' => 'sometimes|required|in:student,faculty',
        ]);

        $author->update($request->all());

        return response()->json($author);
    }

    public function destroy(Author $author)
    {
        if ($author->thesis_count > 0) {
            return response()->json([
                'message' => 'Cannot delete author with associated theses.'
            ], 422);
        }

        $author->delete();

        return response()->json(['message' => 'Author deleted successfully.']);
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $authors = Author::search($request->q)
            ->limit(10)
            ->get(['id', 'full_name', 'department', 'author_type']);

        return response()->json($authors);
    }

    public function suggestions(Request $request)
    {
        $request->validate([
            'last_name' => 'required|string|min:2',
        ]);

        $authors = Author::where('last_name', 'LIKE', "{$request->last_name}%")
            ->limit(10)
            ->get(['id', 'full_name', 'last_name', 'first_name', 'middle_initial']);

        return response()->json($authors);
    }
}
