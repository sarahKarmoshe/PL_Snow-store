<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Product $product)
    {
        if($product->owner_id=Auth::id())
        {

            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'=>'required|string',
            'price'=>'required|numeric',
            'quantity'=>'required|numeric',
            'exp_date'=>'required|date',
            'description'=>'required',
            'category_id'=>'required',
            'product_image' => 'required|image:jpeg,png,jpg,gif,svg',

        ];
    }
}
