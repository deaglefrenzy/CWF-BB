<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Post $post)
    {
        request()->validate([
            'content' => ['required', 'min:1'],
        ]);

        $comment = Comment::create([
            "user_id" => $post->user_id,
            "post_id" => $post->id,
            "content" => request("content")
        ]);

        return response()->json(["message" => "comment added to post " . $post->id, "data" => $comment]);
    }

    public function show(Post $id)
    {
        $post = Post::with('comments')->find($id);
        return response()->json(["message" => "displaying comments from post with id " . $post->id, "data" => $post->comments]);
    }

    public function update(Post $post, Comment $comment)
    {
        request()->validate([
            'content' => ['required', 'min:1']
        ]);

        $comment->update([
            "content" => request("content")
        ]);

        return response()->json(["message" => "comment updated", "data" => $comment->content]);
    }

    public function destroy(Post $post, Comment $comment)
    {
        $comment->delete();

        return response()->json(['message' => "Comment deleted"]);
    }
}
