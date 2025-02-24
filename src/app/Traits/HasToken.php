<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

trait HasToken
{
    public function idCheck(Model $model, Request $request)
    {
        $isAdmin = $this->isAdmin($request);
        $userId = $this->getUserFromToken($request)->id;
        if ($model->user_id !== $userId && !$isAdmin) {
            $modelName = class_basename($model);
            abort(403, 'Anda bukan pemilik ' . $modelName . ' ini.');
        }
        return true;
    }

    public function getUserFromToken(Request $request): User
    {
        $token = $request->header('Authorization');
        $tokenRecord = DB::table('tokens')->where('token', $token)->first();

        if (!$tokenRecord) {
            return null;
        }

        $user = User::find($tokenRecord->user_id);
        return $user;
    }

    public function isUser(Request $request)
    {
        return (bool) $this->getUserFromToken($request);
    }

    public function isAdmin(Request $request)
    {
        $user = $this->getUserFromToken($request);
        return $user && $user->is_admin;
    }

    public function headBoardCheck(Request $request)
    {
        $isAdmin = $this->isAdmin($request);
        $user = $this->getUserFromToken($request);
        if ($isAdmin || ($user && $user->is_head)) {
            abort(403, 'User bukan kepala bagian.');
        }
        return true;
    }

    public function userBoardCheck(Request $request, int $post_board_id)
    {
        $isAdmin = $this->isAdmin($request);
        $user = $this->getUserFromToken($request);
        if ($isAdmin || ($user && $user->board_id === $post_board_id)) {
            abort(403, 'User bukan di bagian yang cocok.');
        }
        return true;
    }
}
