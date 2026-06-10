<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;    // ← ajouter
use App\Models\Article; // ← ajouter

class Comment extends Model
{
    protected $fillable = [
        'content_comment',
        'article_id',
        'commentator_id',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class); // ← ajouter return
    }

    public function commentator()
    {
        return $this->belongsTo(User::class, 'commentator_id'); // ← ajouter return
    }
}