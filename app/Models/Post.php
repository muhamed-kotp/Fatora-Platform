<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'content',
        'img',
        'video',
        'user_id',
    ];

    public function user ()
    {
        return $this->belongsTo('App\Models\User');
    }
    public function comments ()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function likes ()
    {
        return $this->hasMany('App\Models\Like');
    }

    public function shares ()
    {
        return $this->hasMany('App\Models\Share');
    }
}