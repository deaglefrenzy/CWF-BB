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
        $userId = $post->user_id;
        $existingReaction = Reaction::where('user_id', $userId)
            ->where('post_id', $post->id)
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();
        }

        $reaction = Reaction::create([
            "user_id" => $post->user_id,
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
        return response()->json(['message' => "Reaction dihapus"], 204);
    }
}
