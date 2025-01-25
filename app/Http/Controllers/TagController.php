<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::get();
        return response()->json(["message" => "Tag index", "data" => $tags]);
    }

    public function show(string $tag)
    {
        $posts = Post::whereHas('tags', function ($query) use ($tag) {
            $query->where('name', $tag);
        })->with('tags')->get();

        return response()->json(["message" => "Post with tag " . $tag, "data" => $posts]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z0-9_]+$/', 'unique:tags,name']
        ];

        $messages = [
            'name.required' => 'The tag name is required.',
            'name.string' => 'The tag name must be a string.',
            'name.max' => 'The tag name may not be greater than 15 characters.',
            'name.regex' => 'The tag name must contain only alphanumeric characters or underscores.',
            'name.unique' => 'Duplicate tag name.',
        ];
        try {
            $validatedData = $request->validate($rules, $messages);

            $tag = Tag::create($validatedData);

            return response()->json([
                'message' => 'Tag created successfully',
                'data' => $tag,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->json(['message' => "Tag deleted"], 204);
    }
}
