<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;

class ArticleController extends Controller
{
    // ✅ LISTE
  public function index(Request $request)
{
    // ✅ Récupérer user avec sanctum optionnel
    $user = auth('sanctum')->user();

    Log::info('User dans index : ' . ($user ? $user->id : 'non connecté'));

    $publicArticles = Article::where('visibility', 'public')
        ->with(['author', 'comments', 'likes'])
        ->withCount('likes')
        ->get();

    if ($user) {
        $privateArticles = Article::where('visibility', 'private')
            ->whereHas('accesses', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['author', 'comments', 'likes'])
            ->withCount('likes')
            ->get();

        $myPrivateArticles = Article::where('visibility', 'private')
            ->where('autor_id', $user->id)
            ->with(['author', 'comments', 'likes'])
            ->withCount('likes')
            ->get();

        $articles = $publicArticles
            ->merge($privateArticles)
            ->merge($myPrivateArticles)
            ->unique('id');
    } else {
        $articles = $publicArticles;
    }

    $sortBy   = request('sort', 'created_at');
    $articles = match($sortBy) {
        'likes'      => $articles->sortByDesc('likes_count'),
        'count_view' => $articles->sortByDesc('count_view'),
        default      => $articles->sortByDesc('created_at'),
    };

    return response()->json([
        'articles' => ArticleResource::collection($articles->values())
    ]);
}
    // ✅ CRÉER
    public function store(StoreArticleRequest $request)
    {
        Log::info('User mode : ' . $request->user()->mode);
        Log::info('User id : '   . $request->user()->id);
        if ($request->user()->mode !== 'author') {
            return response()->json([
                'message' => 'Passez en mode auteur pour écrire'
            ], 403);
        }

        $article = Article::create([
            'title'      => $request->title,
            'content'    => $request->content,
            'visibility' => $request->visibility ?? 'public',
            'count_view' => 0,
            'autor_id'   => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Article créé avec succès',
            'article' => new ArticleResource($article->load('author', 'comments', 'likes')),
        ], 201);
    }

    // ✅ AFFICHER
    public function show(Request $request, Article $article)
{
    if ($article->visibility === 'private') {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Connectez-vous'
            ], 401);
        }

        $isAuthor  = $article->autor_id === $request->user()->id;
        $isAdmin   = $request->user()->role === 'admin';
        $hasAccess = ArticleAccess::where('article_id', $article->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if (!$isAuthor && !$isAdmin && !$hasAccess) {
            return response()->json([
                'message' => 'Accès non autorisé'
            ], 403);
        }
    }

    $article->increment('count_view');

    // ✅ Retourner sans enveloppe data
    return response()->json(
        (new ArticleResource(
            $article->load('author', 'comments.commentator', 'likes.liker')
        ))->resolve()
    );
}

    // ✅ MODIFIER
    public function update(UpdateArticleRequest $request, Article $article)
    {
        if ($article->autor_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Action non autorisée'
            ], 403);
        }

        $article->update($request->only([
            'title', 'content', 'visibility'
        ]));

        return response()->json([
            'message' => 'Article modifié avec succès',
            'article' => new ArticleResource($article->load('author', 'comments', 'likes')),
        ]);
    }

    // ✅ SUPPRIMER
    public function destroy(Request $request, Article $article)
    {
        if ($article->autor_id !== $request->user()->id
            && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Action non autorisée'
            ], 403);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article supprimé avec succès'
        ]);
    }

    

    // ✅ Mes articles
public function myArticles(Request $request)
{
    $articles = Article::where('autor_id', $request->user()->id)
        ->with(['author', 'comments', 'likes'])
        ->withCount('likes')
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'articles' => ArticleResource::collection($articles)
    ]);
}

// ✅ GET — Liste des utilisateurs ayant accès
public function getAccess(Request $request, Article $article)
{
    if ($article->autor_id !== $request->user()->id) {
        return response()->json([
            'message' => 'Action non autorisée'
        ], 403);
    }

    $users = $article->accesses()
        ->with('user')
        ->get()
        ->map(fn($access) => [
            'id'    => $access->user->id,
            'name'  => $access->user->name,
            'email' => $access->user->email,
        ]);

    return response()->json(['users' => $users]);
}

// ✅ POST — Donner accès par email
public function grantAccess(Request $request, Article $article)
{
    if ($article->autor_id !== $request->user()->id) {
        return response()->json([
            'message' => 'Action non autorisée'
        ], 403);
    }

    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    // Vérifier que c'est pas l'auteur lui-même
    if ($user->id === $request->user()->id) {
        return response()->json([
            'message' => 'Vous êtes déjà l\'auteur de cet article'
        ], 403);
    }

    ArticleAccess::firstOrCreate([
        'article_id' => $article->id,
        'user_id'    => $user->id,
    ]);

    return response()->json([
        'message' => 'Accès accordé à ' . $user->name,
    ]);
}

// ✅ DELETE — Révoquer accès
public function revokeAccess(Request $request, Article $article, $userId)
{
    if ($article->autor_id !== $request->user()->id) {
        return response()->json([
            'message' => 'Action non autorisée'
        ], 403);
    }

    ArticleAccess::where('article_id', $article->id)
        ->where('user_id', $userId)
        ->delete();

    return response()->json([
        'message' => 'Accès révoqué avec succès'
    ]);
}
}