<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Laratrust\Traits\LaratrustUserTrait;

class User extends Authenticatable
{
    use LaratrustUserTrait;
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function findAndValidateForPassport($username, $password)
    {
        $user = $this->where('username', $username)->first();

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }
    }
    public function dealers()
    {
        return $this->hasOne('App\Dealer', 'id', 'dealer_id');
    }
    // public function roles()
    // {
    //     return $this->hasOne('App\Role', 'id', 'role_id');
    //     //return $this->hasmany('App\Role', 'id', 'role_id');
    // }
}
