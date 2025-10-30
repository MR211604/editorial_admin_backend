<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    //
    use Notifiable;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'token',
        'active',
        'password',
        'join_date',
        'last_login_date',
        'rol_id'
    ];

    protected $hidden = [
        'password', // Oculta el campo en respuestas JSON
    ];

    public function rol()
    {
        return $this->belongsTo('App\Rol', 'rol_id');
    }

    public function userRoles()
    {
        return $this->belongsToMany('App\Rol', 'rol_users', 'user_id', 'rol_id');
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
}
