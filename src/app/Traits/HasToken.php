<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use PhpParser\Builder\Class_;

trait HasToken
{
    protected function getUserFromToken(Request $request): User
    {
        $token = $request->header('Authorization');

        $tokenRecord = DB::table('tokens')->where('token', $token)->first();

        //return $tokenRecord ? $tokenRecord->user_id : null;

        if (!$tokenRecord) {
            return null; // Token not found
        }

        // Fetch the user associated with the token
        $user = User::find($tokenRecord->user_id);

        return $user; // Return the user object or null if not found
    }

    public function idCheck(Model $model, Request $request)
    {
        $userId = $this->getUserFromToken($request)->id;
        if ($model->user_id !== $userId && !$this->isAdmin($request)) {
            $modelName = class_basename($model);
            abort(403, 'Unauthorized. You do not own this ' . $modelName . '.');
        }
        return true;
    }

    protected function isAdmin(Request $request)
    {
        // Assuming you have a method to get the user from the request
        $user = $this->getUserFromToken($request);

        // Check if the user is an admin
        return $user && $user->is_admin;
    }
}
