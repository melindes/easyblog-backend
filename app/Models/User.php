<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'mode',
        'is_blocked'
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Ajouter les relations dans User.php
public function articles()
{
    return $this->hasMany(Article::class, 'autor_id');
}

public function comments()
{
    return $this->hasMany(Comment::class, 'commentator_id');
}

public function likes()
{
    return $this->hasMany(Like::class, 'liker_id');
}

public function articleAccesses()
{
    return $this->hasMany(ArticleAccess::class);
}
}