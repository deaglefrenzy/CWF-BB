<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Traits\HasToken;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PostsController extends Controller
{
    use HasToken;

    public function index(Request $request)
    {
        $user = $this->getUserFromToken($request);
        $query = Post::query();

        if (!$user) {
            $query->where('board_id', 1);
        } else if ($this->isAdmin($request)) {
        } else {
            $query->where(function ($q) use ($user) {
                $q->whereIn('board_id', [1, 2])
                    ->orWhere('board_id', $user->board_id);
            });
        }

        $posts = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $response = [
            "message" => "Publik, Pengumuman, dan Post Bagian",
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
        $posts = Post::where('board_id', '1')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $response = [
            "message" => "Semua post Publik",
            "current_page" => $posts->currentPage(),
            "per_page" => $posts->perPage(),
            "total" => $posts->total(),
            "last_page" => $posts->lastPage(),
            "next_page" => $posts->nextPageUrl(),
            "previous_page" => $posts->previousPageUrl(),
            "data" => $posts->items()
        ];

        if ($posts->isEmpty()) {
            return response()->json(["message" => "Tidak ada post Publik", "data" => []], 404);
        }

        return response()->json($response);
    }

    public function show($id, Request $request)
    {
        $user = $this->getUserFromToken($request);
        $this->userBoardCheck($request, $id);

        if ($user) {
            $viewerIdentifier = $user->id;
        } else {
            $viewerIdentifier = request()->ip();
        }

        $post = Post::with([
            'reactions' => function ($query) {
                $query->select('id', 'emoji', 'post_id', 'user_id')
                    ->with('user:id,username');
            },
            'comments' => function ($query) {
                $query->select('id', 'content', 'post_id',  'user_id', 'created_at')
                    ->with('user:id,username');
            },
            'user:id,username,fullname',
            'board:id,name'
        ])->findOrFail($id);

        $viewExists = DB::table('post_views')
            ->where('post_id', $post->id)
            ->where(function ($query) use ($viewerIdentifier, $user) {
                if ($user) {
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
                'user_id' => $user ? $viewerIdentifier : null,
                'ip_address' => !$user ? $viewerIdentifier : null,
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
            // 'tags_id' => ['exists:tags,id'],
            'board_id' => ['required', 'integer', 'exists:boards,id']
        ];

        $messages = [
            'title.required' => 'Harus punya judul.',
            'title.min' => 'Judul minimal 3 karakter.',
            'body.required' => 'Harus punya isi post.',
            'user_id.required' => 'Harus ada user.',
            'user_id.exists' => 'User tidak ditemukan.',
            'board_id.required' => 'Harus ada board.',
            'board_id.exists' => 'Bagian tidak ditemukan.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);

            $this->headBoardCheck($request);
            $this->userBoardCheck($request, $validatedData["board_id"]);

            $post = Post::create(Arr::except($validatedData, ['tags_id']));

            // if (isset($validatedData['tags_id']) && !empty($validatedData['tags_id'])) {
            //     $post->tags()->attach($validatedData['tags_id']);
            // }

            $post->refresh();

            $newpost = Post::find($post->id);
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
            // 'tags_id' => ['exists:tags,id'],
            'board_id' => ['required', 'integer', 'exists:board,id']
        ];

        $messages = [
            'title.required' => 'Harus punya judul.',
            'title.min' => 'Judul minimal 3 karakter.',
            'body.required' => 'Harus punya isi post.',
            'board_id.required' => 'Harus ada bagian.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);

            $post->update(Arr::except($validatedData, ['tags_id']));

            // if (isset($validatedData['tags_id'])) {
            //     $post->tags()->sync($validatedData['tags_id']);
            // } else {
            //     $post->tags()->sync([]);
            // }

            $post->refresh();
            $newpost = Post::find($post->id);

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
        if (($this->idCheck($post, $request)) || $this->headBoardCheck($request)) {
            //$post->tags()->detach();
            $post->comments()->delete();
            $post->reactions()->delete();
            $post->delete();
        }

        return response()->json(['message' => "Post dihapus"], 204);
    }
}
