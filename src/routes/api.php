<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\BoardController;
use Illuminate\Support\Facades\Route;

Route::middleware(["LoggedIn"])->group(function () {

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store'])->middleware('Admin');
    Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('Admin');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('Admin');

    Route::get('/posts', [PostsController::class, 'index']);
    Route::get('/posts/{post}', [PostsController::class, 'show']);
    Route::post('/posts', [PostsController::class, 'store']);
    Route::patch('/posts/{post}', [PostsController::class, 'update']);
    Route::delete('/posts/{post}', [PostsController::class, 'destroy']);

    Route::post('/posts/{post}/tag', [PostsController::class, 'attach']);

    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::patch('/posts/{post}/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/posts/{post}/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/posts/{post}/reaction', [ReactionController::class, 'store']);
    Route::delete('/posts/{post}/reaction/{reaction}', [ReactionController::class, 'destroy']);

    Route::get('/board', [BoardController::class, 'index']);
    Route::get('/board/{board:name}', [BoardController::class, 'show']);

    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/tags/{tag:name}', [TagController::class, 'show']);
    Route::post('/tags', [TagController::class, 'store'])->middleware('Admin');
    Route::patch('/tags/{tag}', [TagController::class, 'update'])->middleware('Admin');
    Route::delete('/tags/{tag:name}', [TagController::class, 'destroy'])->middleware('Admin');

    Route::post('/logout', [LoginController::class, 'destroy']);
});

Route::post('/login', [LoginController::class, 'store'])->name("login");
