<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Traits\HasToken;
use Illuminate\Support\Arr;

class PostsController extends Controller
{
    use HasToken;

    public function index()
    {
        $posts = Post::get();
        return response()->json(["message" => "Semua post", "data" => $posts]);
        //return response()->json(["message" => "Displaying all posts", "posts" => PostResource::collection($posts)]);
    }

    public function publik()
    {
        $posts = Post::whereHas('tags', function ($query) {
            $query->where('name', 'Publik');
        })->get();

        if ($posts->isEmpty()) {
            return response()->json(["message" => "Tidak ada post dengan tag Publik", "data" => []], 404);
        }

        return response()->json(["message" => "Semua post dengan tag Publik", "data" => $posts]);
    }

    public function show($id)
    {
        $post = Post::with('comments', 'tags')->findOrFail($id);
        $duration = request('duration');

        if (!is_null($duration) && is_numeric($duration) && $duration >= 5) {
            $post->viewcount++;
            $post->save();
        }

        return response()->json(["message" => "Post I " . $post->id, "data" => $post]);
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'title' => ['required', 'string', 'min:3'],
            'body' => ['required', 'string'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];

        $messages = [
            'title.required' => 'Harus punya judul.',
            'title.min' => 'Judul minimal 3 karakter.',
            'body.required' => 'Harus punya isi post.',
            'user_id.required' => 'Harus ada user.',
            'user_id.exists' => 'User tidak ditemukan.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);
            $post = Post::create($validatedData);

            return response()->json([
                'message' => "Post dibuat dengan ID: {$post->id}",
                'data' => $post,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function update(Post $post, Request $request)
    {

        $this->idCheck($post, $request);

        $rules = [
            'title' => ['required', 'string', 'min:3'],
            'body' => ['required', 'string'],
        ];

        $messages = [
            'title.required' => 'Harus punya judul.',
            'title.min' => 'Judul minimal 3 karakter.',
            'body.required' => 'Harus punya isi post.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);
            $post->update($validatedData);

            return response()->json([
                'message' => "Post terupdate. ID: {$post->id}",
                'data' => $post,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(Post $post, Request $request)
    {
        $this->idCheck($post, $request);
        $post->delete();

        return response()->json(['message' => "Post dihapus"], 204);
    }

    public function attach(Post $post)
    {
        $tagName = request('name');
        $tag = Tag::firstOrCreate(['name' => $tagName]);

        $post->tags()->syncWithoutDetaching($tag->id);

        return response()->json(['message' => 'Tag ditambahkan ke post', 'data' => $post]);
    }
}
