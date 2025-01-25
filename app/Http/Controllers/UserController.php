<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $attributes = request()->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);
        User::create($attributes);
        return response()->json(['message' => 'User created', 'data' => $attributes]);
    }
}
