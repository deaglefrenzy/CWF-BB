<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use App\Models\Post;
use App\Traits\HasToken;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    use HasToken;

    public function store(Request $request, Post $post)
    {
        $user_id = $this->getUserFromToken($request)->id;
        $existingReaction = Reaction::where('user_id', $user_id)
            ->where('post_id', $post->id)
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();
        }

        $reaction = Reaction::create([
            "user_id" => $user_id,
            "post_id" => $post->id,
            "emoji" => request("emoji")
        ]);

        return response()->json(["message" => "Reaction ditambahkan ke post " . $post->id, "data" => $reaction], 201);
    }

    public function destroy(Post $post, Reaction $reaction, Request $request)
    {
        if (($this->idCheck($reaction, $request)) || $this->headBoardCheck($request)) {
            $reaction->delete();
        }
        return response()->json(['message' => "Reaction dihapus"], 200);
    }
}
