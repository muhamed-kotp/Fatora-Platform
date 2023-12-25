<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'img',
        'phone',
        'address',
        'oauth_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function posts ()
    {
        return $this->hasMany('App\Models\Post');
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

    public function sendFrindRequest()
    {
        return $this->belongsToMany('App\Models\User', 'relationships', 'user1_id', 'user2_id');
    }

    public function recieveFrindRequest()
    {
        return $this->belongsToMany('App\Models\User', 'relationships', 'user2_id', 'user1_id');
    }
    public function friends()
    {
        return $this->belongsToMany('App\Models\User', 'friends', 'friend1_id', 'friend2_id');
    }
    public function friendships()
    {
        return $this->belongsToMany('App\Models\User', 'friends', 'friend2_id', 'friend1_id');
    }


}