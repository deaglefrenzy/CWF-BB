<?php

use App\Http\Middleware\Admin;
use App\Http\Middleware\CheckToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php', // Add api route here
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'LoggedIn' => CheckToken::class,
            'Admin' => Admin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


// $myCarCollections = [
//     new Car(speed: 2, name: "Nissan"),
//     new Car(speed: 1, name: "Toyota"),
// ];

// $myFavoriteNumbers = [3, 6, 4, 10]

// function mySort($array, $sortLogic) {

//     foreach($element as $array) {
//         $sortLogic($element);
//     }
//     ECHO "sorted"
// }

// theBestSortingF = function ($car1, $car2) {
// }

// $myCarCollectionSorted = mySort($myCarCollections, theBestSo);

// $mySortedFavoritedNumbers = mySort($myFavoriteNumbers, function ($num1, $num2) {

// });
