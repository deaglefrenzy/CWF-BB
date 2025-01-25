<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Models\Reaction;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(["LoggedIn"])->group(function () {

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

    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/tags/{tag:name}', [TagController::class, 'show']);
    Route::post('/tags', [TagController::class, 'store'])->middleware('Admin');
    Route::delete('/tags/{tag:name}', [TagController::class, 'destroy'])->middleware('Admin');

    Route::post('/logout', [LoginController::class, 'destroy']);
});

Route::post('/login', [LoginController::class, 'store'])->name("login");
