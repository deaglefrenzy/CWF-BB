<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use App\Models\Post;
use App\Traits\HasToken;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    use HasToken;

    public function store(Post $post)
    {
        $reaction = Reaction::create([
            "user_id" => $post->user_id,
            "post_id" => $post->id,
            "emoji" => request("emoji")
        ]);

        return response()->json(["message" => "Reaction added to post " . $post->id, "data" => $reaction], 201);
    }

    public function destroy(Post $post, Reaction $reaction, Request $request)
    {
        $this->idCheck($reaction, $request);
        $reaction->delete();
        return response()->json(['message' => "Reaction deleted from", "data" => $post], 204);
    }
}
