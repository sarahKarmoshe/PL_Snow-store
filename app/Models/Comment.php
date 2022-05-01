<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table='comments';
    protected $primaryKey='id';
    protected $fillable=['comment','product_id','owner_id'];


    public  function product(){
        return $this->belongsTo(\App\Models\Product::class);
    }

    public  function user(){
        return $this->belongsTo(\App\Models\User::class);
    }

}
