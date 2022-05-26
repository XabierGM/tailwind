<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', [\App\Http\Controllers\PostController::class, 'home'])->name('home');
Route::get('/posts/{slug}', [\App\Http\Controllers\PostController::class, 'detail'])->name('posts.detail');
Route::post('/comment', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
Route::resource('/admin/posts', \App\Http\Controllers\PostController::class);
Auth::routes();
