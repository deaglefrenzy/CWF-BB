<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use App\Models\Post;

class ReactionController extends Controller
{
    public function store(Post $post)
    {
        $reaction = Reaction::create([
            "user_id" => $post->user_id,
            "post_id" => $post->id,
            "emoji" => request("emoji")
        ]);

        return response()->json(["message" => "Reaction added to post " . $post->id, "data" => $reaction]);
    }

    public function destroy(Post $post, Reaction $reaction)
    {
        $reaction->delete();
        return response()->json(['message' => "Reaction deleted from", "data" => $post]);
    }
}
