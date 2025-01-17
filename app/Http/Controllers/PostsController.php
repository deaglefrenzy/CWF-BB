<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;

class PostsController extends Controller
{
    public function index()
    {
        $posts = Post::with('comments', 'tags')->get();
        //return response()->json($posts);
        //return response()->json(["data" => $posts]);
        return response()->json(["message" => "Displaying all posts", "posts" => PostResource::collection($posts)]);
    }

    public function show(string $id)
    {
        $post = Post::with('comments', 'tags')->find($id);
        return response()->json(["message" => "displaying post with the id " . $post->id, "post" => $post]);
    }

    public function store()
    {
        // if (Auth::guest()) {
        //     return response()->json("must be logged in", 401);
        //     die(1);
        // }

        request()->validate([
            'title' => ['required', 'min:3'],
            'body' => ['required'],
        ]);

        $post = Post::create([
            "title" => request("title"),
            "body" => request("body"),
            "user_id" => request("user_id")
        ]);

        return response()->json(["message" => "post created " . $post->id, "post" => $post]);
    }

    // HIGH ORDER FUNCTION
    // f(x) = x + 1
    // y = f(x)
    // y = x + 1
    // f(f(x)) = f(x) + 1

    public function update(Post $post)
    {

        // if (Gate::denies('update-post', $post)) {
        //     return response()->json("not your post");
        // }

        request()->validate([
            'title' => ['required', 'min:3'],
            'body' => ['required'],
        ]);


        $post->update([
            "title" => request("title"),
            "body" => request("body"),
        ]);

        return response()->json(["message" => "post updated", "updated post" => $post]);
    }

    public function destroy(Post $post)
    {
        // if (Gate::denies('update-post', $post)) {
        //     return response()->json("not your post");
        // }

        $post->delete();

        return response()->json(['message' => "Post deleted"]);
    }
}
