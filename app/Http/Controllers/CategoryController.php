<?php

namespace App\Http\Controllers;

use App\Models\Category;
use http\Env\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index()
    {
        $categories=Category::all();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validator=Validator::make($request->all(),['name'=>'required|string']);

        if($validator->fails())
            return response()->json($validator->errors());

        $name=$request->name;
        $category= Category::query()->create(['name'=>$name]);

        return response()->json($category->id);

    }


}
