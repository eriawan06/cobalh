<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory;

    protected $fillable = [
        'fullname', 'username', 'email', 'phone', 'role', 
        'gender', 'birth_date', 'password' 
    ];

    protected $hidden = [
        'password',
    ];

    public $timestamps = true;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function hasRole($roles)
    {
        foreach ($roles as $role) {
            if ($this->role == $role) {
                return true;
            }
        }
        return false;
    }
}
