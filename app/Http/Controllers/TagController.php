<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::get();
        return response()->json(["message" => "Displaying all tags", "datas" => $tags]);
    }

    public function show(string $tag)
    {
        $posts = Post::whereHas('tags', function ($query) use ($tag) {
            $query->where('name', $tag);
        })->with('tags')->get();

        return response()->json(["message" => "get posts with the tag " . $tag, "data" => $posts]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:8|regex:/^[a-zA-Z0-9]+$/',
        ]);

        $tag = Tag::create([
            "name" => $request->name
        ]);

        return response()->json(["message" => "tag created", "data" => $tag]);
    }

    public function destroy()
    {
        request("name")->delete();

        return response()->json(['message' => "Tag deleted"]);
    }


    public function attach(Request $request, Post $post)
    {
        $tag = Tag::firstOrCreate(['name' => $request->tag_name]);

        $post->tags()->syncWithoutDetaching($tag->id);

        return response()->json(['message' => 'Tag attached to post successfully!']);
    }
    // public function __invoke(Tag $tag)
    // {
    //     return response()->json(['message' => 'displaying posts with the tag ' . $tag->name, 'posts' => $tag->posts]);
    // }
}
