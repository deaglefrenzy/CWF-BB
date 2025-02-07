<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use PhpParser\Builder\Class_;

trait HasToken
{
    public function getUserIdFromToken(Request $request): ?int
    {
        $token = $request->header('Authorization');

        $tokenRecord = DB::table('tokens')->where('token', $token)->first();

        return $tokenRecord ? $tokenRecord->user_id : null;
    }

    public function idCheck(Model $model, Request $request)
    {
        $userId = $this->getUserIdFromToken($request);
        if ($model->user_id !== $userId) {
            $modelName = class_basename($model);
            abort(403, 'Unauthorized. You do not own this ' . $modelName . '.');
        }
        return true;
    }
}
