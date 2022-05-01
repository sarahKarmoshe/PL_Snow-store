<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name', 'price', 'current_price',
        'description', 'img_url', 'quantity',
        'views', 'exp_date', 'owner_id', 'category_id'];

    public $withCount = ['likes', 'comments'];
    public $with = ['user','comments'];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class , 'owner_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment ::class, 'product_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'product_id');
    }

    public function discounts()
    {
        return $this->hasMany(Discount ::class, 'product_id');
    }


}
