<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\Request;

class PostsController extends Controller
{
    public function index()
    {
        //$posts = Post::with('comments', 'tags')->get();
        $posts = Post::get();
        return response()->json(["message" => "Displaying all posts", "data" => $posts]);
        //return response()->json(["message" => "Displaying all posts", "posts" => PostResource::collection($posts)]);
    }

    public function show($id)
    {
        $post = Post::with('comments', 'tags')->findOrFail($id);
        $duration = request('duration');

        if ($duration >= 5) {
            $post->viewcount++;
            $post->save();
        }

        return response()->json(["message" => "Post id " . $post->id, "data" => $post]);
    }

    public function store()
    {

        request()->validate([
            'title' => ['required', 'min:3'],
            'body' => ['required'],
        ]);

        $post = Post::create([
            "title" => request("title"),
            "body" => request("body"),
            "user_id" => request("user_id")
        ]);

        return response()->json(["message" => "post created " . $post->id, "data" => $post]);
    }

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

        return response()->json(["message" => "post updated", "data" => $post]);
    }

    public function destroy(Post $post)
    {
        // if (Gate::denies('update-post', $post)) {
        //     return response()->json("not your post");
        // }

        $post->delete();

        return response()->json(['message' => "Post deleted"]);
    }

    public function attach(Post $post)
    {
        $tagName = request('name');
        $tag = Tag::firstOrCreate(['name' => $tagName]);

        $post->tags()->syncWithoutDetaching($tag->id);

        return response()->json(['message' => 'Tag attached to post successfully!']);
    }
}
