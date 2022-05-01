<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $table='discounts';
    protected $primaryKey='id';
    protected $fillable=['percentage','product_id','number_of_days'];

    public  function product(){
        return $this->belongsTo(\App\Models\Product::class);
    }

}
