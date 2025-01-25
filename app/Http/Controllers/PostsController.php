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
        return response()->json(["message" => "All posts", "data" => $posts]);
        //return response()->json(["message" => "Displaying all posts", "posts" => PostResource::collection($posts)]);
    }

    public function show($id)
    {
        $post = Post::with('comments', 'tags')->findOrFail($id);
        $duration = request('duration');

        if (!is_null($duration) && is_numeric($duration) && $duration >= 5) {
            $post->viewcount++;
            $post->save();
        }

        return response()->json(["message" => "Post id " . $post->id, "data" => $post]);
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'title' => ['required', 'string', 'min:3'],
            'body' => ['required', 'string'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];

        $messages = [
            'title.required' => 'The title is required.',
            'title.min' => 'The title must be at least 3 characters.',
            'body.required' => 'The body is required.',
            'user_id.required' => 'A valid user ID is required.',
            'user_id.exists' => 'The specified user ID does not exist.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);
            $post = Post::create($validatedData);

            return response()->json([
                'message' => "Post created successfully with ID: {$post->id}",
                'data' => $post,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
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
            'title.required' => 'The title is required.',
            'title.min' => 'The title must be at least 3 characters.',
            'body.required' => 'The body is required.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);
            $post->update($validatedData);

            return response()->json([
                'message' => "Post updated with ID: {$post->id}",
                'data' => $post,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(Post $post, Request $request)
    {
        $this->idCheck($post, $request);
        $post->delete();

        return response()->json(['message' => "Post deleted"], 204);
    }

    public function attach(Post $post)
    {
        $tagName = request('name');
        $tag = Tag::firstOrCreate(['name' => $tagName]);

        $post->tags()->syncWithoutDetaching($tag->id);

        return response()->json(['message' => 'Tag attached to post successfully!']);
    }
}
