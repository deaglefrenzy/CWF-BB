<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Traits\HasToken;

class CommentController extends Controller
{
    use HasToken;

    public function show(Post $id)
    {
        $post = Post::with('comments')->find($id);
        return response()->json(["message" => "Semua komentar dari post: " . $post->id, "data" => $post->comments]);
    }

    public function store(Post $post, Request $request): JsonResponse
    {
        $rules = [
            'content' => ['required', 'string'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];

        $messages = [
            'content.required' => 'Harus ada isi komentar.',
            'user_id.required' => 'Harus ada user.',
            'user_id.exists' => 'User tidak ditemukan.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);
            $comment = Comment::create([
                'content' => $validatedData['content'],
                'post_id' => $post->id,
                'user_id' => $validatedData['user_id']
            ]);

            return response()->json([
                'message' => "Komentar terbuat dengan id: {$comment->id}",
                'data' => $comment,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function update(Post $post, Comment $comment, Request $request)
    {
        $this->idCheck($comment, $request);

        $rules = [
            'content' => ['required', 'string'],
        ];

        $messages = [
            'content.required' => 'Harus ada isi komentar.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);
            $comment->update($validatedData);

            return response()->json([
                'message' => "Komentar diedit: {$comment->id}",
                'data' => $comment,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(Post $post, Comment $comment, Request $request)
    {
        $this->idCheck($comment, $request);

        $comment->delete();

        return response()->json(['message' => "Komentar dihapus dari post"], 204);
    }
}
