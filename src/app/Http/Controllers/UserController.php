<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Traits\HasToken;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use HasToken;

    public function index()
    {
        $users = User::get();
        return response()->json(["message" => "Semua user", "data" => $users]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json(["message" => "Profil user ID " . $user->id, "data" => $user]);
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'username' => ['required', 'string', 'min:5', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
            'fullname' => ['required', 'string'],
            'is_admin' => ['required', 'boolean'],
            'is_head' => ['required', 'boolean'],
            'board_id' => ['required', 'integer']
        ];

        $messages = [
            'username.required' => 'Username harus diisi.',
            'username.min' => 'Username minimal 5 karakter.',
            'username.unique' => 'Username sudah terpakai.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password harus diisi.',
            'fullname.required' => 'Nama lengkap harus diisi.',
            'is_admin.required' => 'Status admin harus diisi.',
            'is_head.required' => 'Status head harus diisi.',
            'board_id.required' => 'Board harus diisi.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);
            $password = $validatedData['password'];
            unset($validatedData['password'], $validatedData['password_confirmation']);

            $user = new User($validatedData);
            $user->password = Hash::make($password);
            $user->save();

            return response()->json([
                'message' => "User dibuat dengan ID: {$user->id}",
                'data' => $user,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function update(User $user, Request $request)
    {
        $rules = [
            'username' => ['required', 'string', 'min:5', 'unique:users,username,' . $user->id],
            'fullname' => ['required', 'string'],
            'is_admin' => ['required', 'boolean'],
            'is_head' => ['required', 'boolean'],
            'board_id' => ['required', 'integer']
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            $rules['password_confirmation'] = ['required', 'string'];
        }

        $messages = [
            'username.required' => 'Username harus diisi.',
            'username.min' => 'Username minimal 5 karakter.',
            'username.unique' => 'Username sudah terpakai.',
            'password.min' => 'Password minimal 8 karakter.',
            'fullname.required' => 'Nama lengkap harus diisi.',
            'is_admin.required' => 'Status admin harus diisi.',
            'is_head.required' => 'Status head harus diisi.',
            'board_id.required' => 'Board harus diisi.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);

            if ($request->filled('password')) {
                $password = $validatedData['password'];
                unset($validatedData['password'], $validatedData['password_confirmation']);
                $user->password = Hash::make($password);
            }

            $user->update($validatedData);

            return response()->json([
                'message' => 'User terupdate',
                'data' => $user
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => "User dihapus"], 204);
    }
}
