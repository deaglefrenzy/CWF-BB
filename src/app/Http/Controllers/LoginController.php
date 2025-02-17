<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:6',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Username atau password salah.'], 401);
        }

        $token = Str::random(60);

        DB::table('tokens')->insert([
            'token' => $token,
            'user_id' => $user->id,
            'created_at' => Carbon::now()->setTimezone('Asia/Makassar'),
            'updated_at' => Carbon::now()->setTimezone('Asia/Makassar'),
            'expires_at' => Carbon::now()->setTimezone('Asia/Makassar')->addHours(24)
        ]);

        return response()->json([
            'message' => 'Login sukses.',
            'token' => $token,
            'data' => $user,
        ]);
    }

    public function destroy(Request $request)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['errors' => 'Token tidak ditemukan.'], 400);
        }

        DB::table('tokens')
            ->where('token', $token)
            ->delete();

        return response()->json([
            'message' => 'Logout sukses.',
        ], 204);
    }
}
