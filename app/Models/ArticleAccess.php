<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleAccess extends Model
{
     protected $table = 'articles_access'; 
    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'user_id',
    ];

    // Accès appartient à un article
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    // Accès appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}