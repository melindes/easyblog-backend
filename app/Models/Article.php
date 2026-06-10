<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\Like;          // ← ajouter
use App\Models\Comment;       // ← ajouter
use App\Models\ArticleAccess; // ← ajouter
use App\Models\User; 

class Article extends Model
{
    protected $fillable = [
        'title',
        'content',
        'count_view',
        'visibility',
        'autor_id'
    ];

    public function author(){
        return $this->belongsTo(User::class,'autor_id');
    }

    public  function comments(){
        return $this->hasMany(Comment::class);
    }

    public function likes(){
        return $this->hasMany(Like::class);
    }

    public function accesses(){
        return $this->hasMany(ArticleAccess::class);
    }
}
