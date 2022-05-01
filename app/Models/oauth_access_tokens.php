<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class oauth_access_tokens extends Model
{
    public  function user(){
        return $this->belongsTo(\App\Models\User::class);
    }

    use HasFactory;
}
