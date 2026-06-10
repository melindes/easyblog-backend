<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    // ✅ COMMENTER — POST /api/articles/{id}/comments
   public function store(StoreCommentRequest $request, Article $article)
{
    if ($article->autor_id === $request->user()->id) {
        return response()->json([
            'message' => 'Vous ne pouvez pas commenter votre propre article'
        ], 403);
    }

    $exists = Comment::where('article_id', $article->id)
        ->where('commentator_id', $request->user()->id)
        ->exists();

    if ($exists) {
        return response()->json([
            'message' => 'Vous avez déjà commenté cet article'
        ], 403);
    }

    $comment = Comment::create([
        'content_comment' => $request->content_comment,
        'article_id'      => $article->id,
        'commentator_id'  => $request->user()->id,
    ]);

    return response()->json([
        'message' => 'Commentaire ajouté',
        'comment' => new CommentResource($comment->load('commentator')),
    ], 201);
}

    // ✅ SUPPRIMER — DELETE /api/comments/{id}
    public function destroy(Request $request, Comment $comment)
    {
        if ($comment->commentator_id !== $request->user()->id
            && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Action non autorisée'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Commentaire supprimé'
        ]);
    }
}