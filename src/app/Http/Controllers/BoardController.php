<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Post;

class BoardController extends Controller
{
    public function index()
    {
        $boards = Board::get();
        return response()->json(["message" => "Semua bagian ", "data" => $boards]);
    }

    public function show(string $board)
    {
        $posts = Post::whereHas('board', function ($query) use ($board) {
            $query->where('name', $board);
        })->with('board')->get();

        return response()->json(["message" => "Semua post di bagian " . $board, "data" => $posts]);
    }
}
