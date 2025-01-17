<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        $user = DB::table('users')
            ->join('tokens', 'users.id', '=', 'tokens.user_id')
            ->where('tokens.token', $token)
            ->first();

        if (!$user || !$user->is_admin) {
            return response()->json(['error' => 'Invalid token or user is not admin'], 401);
        }

        return $next($request);
    }
}
