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
        return response()->json(["message" => "Semua tag", "data" => $tags]);
    }

    public function show(string $tag)
    {
        $posts = Post::whereHas('tags', function ($query) use ($tag) {
            $query->where('name', $tag);
        })->with('tags')->get();

        return response()->json(["message" => "Semua post dgn tag " . $tag, "data" => $posts]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z0-9_]+$/', 'unique:tags,name']
        ];

        $messages = [
            'name.required' => 'Nama tag harus terisi.',
            'name.string' => 'Nama tag harus string.',
            'name.max' => 'Nama tag tidak boleh lebih dari 15 karakter.',
            'name.regex' => 'Nama tag hanya boleh alfanumerik dan garis bawah.',
            'name.unique' => 'Nama tag sudah ada.',
        ];
        try {
            $validatedData = $request->validate($rules, $messages);

            $tag = Tag::create($validatedData);

            return response()->json([
                'message' => 'Tag dibuat.',
                'data' => $tag,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(Tag $tag)
    {
        $tag->posts()->detach();
        $tag->delete();
        return response()->json(['message' => "Tag dihapus"], 204);
    }
}
