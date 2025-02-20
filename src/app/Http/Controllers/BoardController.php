<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BoardController extends Controller
{
    public function index()
    {
        $boards = Board::get();
        return response()->json(["message" => "Semua board", "data" => $boards]);
    }

    public function show(string $board)
    {
        $posts = Post::whereHas('boards', function ($query) use ($board) {
            $query->where('name', $board);
        })->with('boards')->get();

        return response()->json(["message" => "Semua post dgn board " . $board, "data" => $posts]);
    }
}
