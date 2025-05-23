<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Traits\HasToken;
use Carbon\Carbon;
//use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PostsController extends Controller
{
    use HasToken;

    public function index(Request $request)
    {
        $user = $this->getUserFromToken($request);
        $query = Post::query();
        $search = $request->query();

        if (!$user) {
            $query->where('board_id', 1)
                ->where('visible', true);
            $message = "Publik";
        } else if ($search) {
            $mes1 = "";
            $mes2 = "";
            $mes3 = "";
            if (isset($search['title'])) {
                $title = $search['title'];
                $query->where('title', 'like', '%' . $title . '%')
                    ->where('visible', true);
                $mes1 = " judul \"{$title}\"";
            }
            if (isset($search['start']) && (isset($search['end']))) {
                $start = Carbon::parse($search['start'])->startOfDay();
                $end = Carbon::parse($search['end'])->endOfDay();
                $query->whereBetween('created_at', [$start, $end])
                    ->where('visible', true);
                $mes2 = " tanggal " . Carbon::parse($start)->format('d-m-Y') . " sampai " . Carbon::parse($end)->format('d-m-Y');
            }
            if (!$this->isAdmin($request)) {
                $query->where(function ($q) use ($user) {
                    $q->whereIn('board_id', [1, 2])
                        ->orWhere('board_id', $user->board_id);
                })
                    ->where('visible', true);
                $mes3 = " bagian " . Board::find($user->board_id)->name;
            }
            $message = "dengan pencarian" . $mes1 . $mes2 . $mes3;
        } else if ($this->isAdmin($request)) {
            $message = "Admin (Semua Post)";
        } else if ($user->board_id == 4) {
            $message = "HRD (Semua Post)";
        } else {
            $message = "Bagian " . Board::find($user->board_id)->name;
            $query->where(function ($q) use ($user) {
                $q->whereIn('board_id', [1, 2])
                    ->orWhere('board_id', $user->board_id);
            })
                ->where("visible", true);
        }

        $posts = $query->with(
            [
                'user:id,username,fullname',
                'board:id,name'
            ]
        )
            ->where('visible', true)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $posts->getCollection()->transform(function ($post) {
            $post->comment_count = $post->comments()->count();
            $post->reaction_count = $post->reactions()->count();
            return $post;
        });

        $response = [
            "message" => "Post " . $message,
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

    public function dashboard(Request $request)
    {
        $user = $this->getUserFromToken($request);
        $this->headBoardCheck($request);
        $query = Post::query();

        if ($this->isAdmin($request)) {
            $message = "Admin (Semua Post)";
        } else if ($user->board_id == 4) {
            $message = "HRD (Semua Post)";
        } else {
            $message = "Bagian " . Board::find($user->board_id)->name;
            $query->Where('board_id', $user->board_id);
        }

        $posts = $query->with(
            [
                'user:id,username,fullname',
                'board:id,name'
            ]
        )
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $posts->getCollection()->transform(function ($post) {
            $post->comment_count = $post->comments()->count();
            $post->reaction_count = $post->reactions()->count();
            return $post;
        });

        $response = [
            "message" => "Post " . $message,
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

    public function dbpost($id, Request $request)
    {
        $user = $this->getUserFromToken($request);
        $post = Post::findOrFail($id);

        $board_id = Post::findOrFail($id)->board_id;
        if ($board_id > 2) {
            $this->userBoardCheck($request, $board_id);
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

        return response()->json(["message" => "Post ID " . $post->id, "data" => $post]);
    }

    protected function addViewCount($user, int $id)
    {
        if ($user) {
            $viewerIdentifier = $user->id;
        } else {
            $viewerIdentifier = request()->ip();
        }

        $viewExists = DB::table('post_views')
            ->where('post_id', $id)
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
                'post_id' => $id,
                'user_id' => $user ? $viewerIdentifier : null,
                'ip_address' => !$user ? $viewerIdentifier : null,
                'viewed_at' => now()
            ]);

            $post = Post::findOrFail($id);
            $post->viewcount++;
            $post->save();
        }
    }

    public function show($id, Request $request)
    {
        $user = $this->getUserFromToken($request);
        $post = Post::findOrFail($id);

        if (!$post->visible) {
            return response()->json(["message" => "Post ini tidak dapat ditampilkan."], 404);
        }
        $board_id = Post::findOrFail($id)->board_id;
        if ($board_id > 2) {
            $this->userBoardCheck($request, $board_id);
        }

        $this->addViewCount($user, $id);

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

        return response()->json(["message" => "Post ID " . $post->id, "data" => $post]);
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'title' => ['required', 'string', 'min:3'],
            'body' => ['required', 'string'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'board_id' => ['required', 'integer', 'exists:boards,id'],
            'visible' => ['required']
        ];

        $messages = [
            'title.required' => 'Harus punya judul.',
            'title.min' => 'Judul minimal 3 karakter.',
            'body.required' => 'Harus punya isi post.',
            'user_id.required' => 'Harus ada user.',
            'user_id.exists' => 'User tidak ditemukan.',
            'board_id.required' => 'Harus ada board.',
            'board_id.exists' => 'Bagian tidak ditemukan.',
            'visible.required' => 'Status tampilan harus terisi.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);
            $board_id = $validatedData["board_id"];
            $this->headBoardCheck($request);
            if ($board_id > 2) {
                $this->userBoardCheck($request, $board_id);
            }

            $post = Post::create($validatedData);

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
            'board_id' => ['required', 'integer', 'exists:boards,id'],
            'visible' => ['required']
        ];

        $messages = [
            'title.required' => 'Harus punya judul.',
            'title.min' => 'Judul minimal 3 karakter.',
            'body.required' => 'Harus punya isi post.',
            'board_id.required' => 'Harus ada bagian.',
            'visible.required' => 'Status tampilan harus terisi.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);

            $board_id = $validatedData["board_id"];
            $this->headBoardCheck($request);
            if ($board_id > 2) {
                $this->userBoardCheck($request, $board_id);
            }

            $post->update($validatedData);

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
            $post->comments()->delete();
            $post->reactions()->delete();
            DB::table('post_views')->where('post_id', $post->id)->delete();
            $post->delete();
        }

        return response()->json(['message' => "Post dihapus"], 200);
    }

    public function viewcount(Post $post, Request $request)
    {
        $user = $this->getUserFromToken($request);
        $post_id = $post->id;
        $this->addViewCount($user, $post_id);
        $post = Post::findOrFail($post_id)->only("id", "viewcount");
        $post_views = DB::table('post_views')
            ->where('post_id', $post_id)
            ->get()
            ->map(function ($view) {
                return [
                    'user_id' => $view->user_id,
                    'ip_address' => $view->ip_address,
                    'viewed_at' => $view->viewed_at,
                ];
            });
        return response()->json([
            "message" => "Viewcount terupdate untuk Post ID " . $post_id,
            "data" => [
                "post" => $post,
                "post_views" => $post_views
            ]
        ]);
    }
}
