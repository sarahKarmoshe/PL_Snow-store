<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;
use App\Notifications\VerifyApiEmail;

class User extends Authenticatable implements  MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;


    public function sendApiEmailVerificationNotification($verification_code)
    {
        $this->notify(new VerifyApiEmail(Auth::id(),$verification_code)); // my notification
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $table='users';
    protected $primaryKey='id';

    protected $fillable = [
        'name',
        'email',
        'c_password',
        'password',
        'profile_img_url',
        'facebook_url',
        'whatsapp_url',
         'phone',
         'address',

    ];
    public  function comments(){
        return $this->hasMany(Comment ::class,'owner_id');
    }

    public  function likes(){
        return $this->hasMany(Like::class,'owner_id');
    }
    public  function products(){
        return $this->hasMany(Product::class,'owner_id');
    }
    public function oauth_access_tokens(){
        return $this->hasMany(oauth_access_tokens::class,'user_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'c_password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
