<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the token from the Authorization header
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['error' => 'No token provided'], 400);
        }

        $tokenExists = DB::table('tokens')
            ->where('token', $token)
            ->where('expires_at', '>', Carbon::now()->setTimezone('Asia/Makassar'))
            ->exists();


        if (!$tokenExists) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}

// $startEngineImplementation = function (string $brand): string {
//     if ($brand === "Mercedes") {
//         return "VROOM";
//     } else {
//         return "BOOM";
//     }
// };

// class Car
// {
//     public function __construct(string $brand, Closure $startEngine)
//     {
//         echo $startEngine($brand);
//     }
// }

// $car = new Car("Mercedez Bens", $startEngineImplementation);
// $car2 = new Car("Toyota", $startEngineImplementation);
