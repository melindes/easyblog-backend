<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = [
        'article_id',
        'liker_id',
    ];

    // Un like appartient à un article
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    // Un like appartient à un utilisateur
    public function liker()
    {
        return $this->belongsTo(User::class, 'liker_id');
    }
}