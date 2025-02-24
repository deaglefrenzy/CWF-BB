<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Traits\HasToken;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PostsController extends Controller
{
    use HasToken;

    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $response = [
            "message" => "Semua post",
            "current_page" => $posts->currentPage(),
            "per_page" => $posts->perPage(),
            "total" => $posts->total(),
            "last_page" => $posts->lastPage(),
            "next_page" => $posts->nextPageUrl(),
            "previous_page" => $posts->previousPageUrl(),
            "data" => $posts->items()
        ];

        return response()->json($response);
    }
    //return response()->json(["message" => "Displaying all posts", "posts" => PostResource::collection($posts)]);
    public function publik()
    {
        $posts = Post::where('board_id', '1')->get();

        if ($posts->isEmpty()) {
            return response()->json(["message" => "Tidak ada post dengan board Publik", "data" => []], 404);
        }

        return response()->json(["message" => "Semua post dengan board Publik", "data" => $posts]);
    }

    public function show($id, Request $request)
    {
        $post = Post::with([
            'reactions' => function ($query) {
                $query->select('id', 'emoji', 'post_id', 'user_id')
                    ->with('user:id,username');
            },
            'comments' => function ($query) {
                $query->select('id', 'content', 'post_id',  'user_id', 'created_at')
                    ->with('user:id,username');
            },
            'tags:id,name',
            'user:id,username',
            'board:id,name'
        ])->findOrFail($id);

        $user = $this->getUserFromToken($request);
        $isUser = $this->isUser($request);
        if ($isUser) {
            $viewerIdentifier = $user->$id;
        } else {
            $viewerIdentifier = request()->ip();
        }

        $this->userBoardCheck($request, $post->board_id);

        $viewExists = DB::table('post_views')
            ->where('post_id', $post->id)
            ->where(function ($query) use ($viewerIdentifier, $isUser) {
                if ($isUser) {
                    $query->where('user_id', $viewerIdentifier);
                } else {
                    $query->where('ip_address', $viewerIdentifier)
                        ->whereNull('user_id');
                }
            })
            ->exists();

        if (!$viewExists) {
            DB::table('post_views')->insert([
                'post_id' => $post->id,
                'user_id' => $isUser ? $viewerIdentifier : null,
                'ip_address' => !$isUser ? $viewerIdentifier : null,
                'viewed_at' => now()
            ]);

            $post->viewcount++;
            $post->save();
        }

        return response()->json(["message" => "Post ID " . $post->id, "data" => $post]);
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'title' => ['required', 'string', 'min:3'],
            'body' => ['required', 'string'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'tags_id' => ['exists:tags,id'],
            'board_id' => ['required', 'integer', 'exists:boards,id']
        ];

        $messages = [
            'title.required' => 'Harus punya judul.',
            'title.min' => 'Judul minimal 3 karakter.',
            'body.required' => 'Harus punya isi post.',
            'user_id.required' => 'Harus ada user.',
            'user_id.exists' => 'User tidak ditemukan.',
            'board_id.required' => 'Harus ada board.',
            'board_id.exists' => 'Board tidak ditemukan.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);

            $this->headBoardCheck($request);
            $this->userBoardCheck($request, $validatedData["board_id"]);

            $post = Post::create(Arr::except($validatedData, ['tags_id']));

            if (isset($validatedData['tags_id']) && !empty($validatedData['tags_id'])) {
                $post->tags()->attach($validatedData['tags_id']);
            }

            $post->refresh();

            $newpost = Post::with('tags:id,name')->find($post->id);
            return response()->json([
                'message' => "Post dibuat dengan ID: {$post->id}",
                'data' => $newpost,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
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
            'tags_id' => ['exists:tags,id'],
            'board_id' => ['required', 'integer', 'exists:board,id']
        ];

        $messages = [
            'title.required' => 'Harus punya judul.',
            'title.min' => 'Judul minimal 3 karakter.',
            'body.required' => 'Harus punya isi post.',
            'board_id.required' => 'Harus ada board.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);

            $this->headBoardCheck($request);
            $this->userBoardCheck($request, $validatedData["board_id"]);
            $post->update(Arr::except($validatedData, ['tags_id']));

            if (isset($validatedData['tags_id'])) {
                $post->tags()->sync($validatedData['tags_id']);
            } else {
                $post->tags()->sync([]);
            }

            $post->refresh();
            $newpost = Post::with('tags:id,name')->find($post->id);

            return response()->json([
                'message' => "Post terupdate. ID: {$post->id}",
                'data' => $newpost,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(Post $post, Request $request)
    {
        $this->idCheck($post, $request);
        $this->userBoardCheck($request, $post->board_id);
        $post->tags()->detach();
        $post->comments()->delete();
        $post->reactions()->delete();
        $post->delete();

        return response()->json(['message' => "Post dihapus"], 204);
    }
}
