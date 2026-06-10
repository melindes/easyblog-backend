<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Article;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    // ✅ LIKER/UNLIKER — POST /api/articles/{id}/like
    public function toggleLike(Request $request, Article $article)
    {
         if ($article->autor_id === $request->user()->id) {
        return response()->json([
            'message' => 'Vous ne pouvez pas liker votre propre article'
        ], 403);
    }
    
        $like = Like::where('article_id', $article->id)
            ->where('liker_id', $request->user()->id)
            ->first();

        if ($like) {
            $like->delete();
            return response()->json([
                'message' => 'Like retiré',
                'liked'   => false,
            ]);
        }

        Like::create([
            'article_id' => $article->id,
            'liker_id'   => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Article liké',
            'liked'   => true,
        ]);
    }
}