<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ✅ Index — token optionnel
Route::get('/articles', [ArticleController::class, 'index'])
     ->middleware('auth:sanctum')
     ->withoutMiddleware('auth:sanctum');

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout',      [AuthController::class, 'logout']);
    Route::post('/switch-mode', [AuthController::class, 'switchMode']);
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    // Articles
    Route::get('/articles/my', [ArticleController::class, 'myArticles']);
    Route::apiResource('articles', ArticleController::class)
         ->except(['index']);
    Route::get('/articles/{article}/access',
         [ArticleController::class, 'getAccess']);
    Route::post('/articles/{article}/access',
         [ArticleController::class, 'grantAccess']);
    Route::delete('/articles/{article}/access/{userId}',
         [ArticleController::class, 'revokeAccess']);

    // Commentaires
    Route::post('/articles/{article}/comments',
         [CommentController::class, 'store']);
    Route::delete('/comments/{comment}',
         [CommentController::class, 'destroy']);

    // Likes
    Route::post('/articles/{article}/like',
         [LikeController::class, 'toggleLike']);

    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/users',
             [AdminController::class, 'users']);
        Route::post('/users/{user}/block-author',
             [AdminController::class, 'blockAuthor']);
        Route::post('/users/{user}/unblock-author',
             [AdminController::class, 'unblockAuthor']);
    });
});