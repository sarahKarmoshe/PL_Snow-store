<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\jsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return jsonResponse
     */

    public function index(): jsonResponse
    {
        //delete expired products

        $products_for_delete = Product::query()->get();
        $now = Carbon::now();
        foreach ($products_for_delete as $item) {
            $exp = Carbon::parse($item->exp_date);
            $still_valid = $now->diffInDays($exp, false);
            if ($still_valid <= 0) {
                $item->delete();
            }
        }

        $products = Product::query()->get();
        foreach ($products as $product) {
            $discount = $product->discounts()->orderBy('number_of_days', 'asc')->get();
            $now = Carbon::now();
            $exp = Carbon::parse($product->exp_date);
            $still_valid = $now->diffInDays($exp, false);
            $MaxDiscount = 0;
            foreach ($discount as $item) {
                $difference = $item['number_of_days'] - $still_valid;
                if ($difference > 0) {
                    $MaxDiscount = $item['percentage'];
                    break;
                }
            }
            $discount_value = ($product->price * $MaxDiscount * 0.01);
            $product->current_price = $product->price - $discount_value;
        }
        return response()->json($products);
    }

    public function savePhoto($photo): string
    {
        $fileExtionsion = $photo->getClientOriginalExtension();
        $fileName = time() . '.' . $fileExtionsion;
        $path = 'images/products';
        $photo->move($path, $fileName);
        return $fileName;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return jsonResponse
     */
    public function store(StoreProductRequest $request): jsonResponse
    {
        //another way to save photo
//        $uploadFolder = 'products';
//        $image = $request->file('product_image');
//        $image_uploaded_path = $image->store($uploadFolder, 'public');
//        $image_url = Storage::disk('public')->url($image_uploaded_path);
//
        //this Url changes up to the host
        $path = "http://localhost:8000/images/products/";
        $image_url = $path . '' . $this->savePhoto($request->product_image);

        $product = Product::query()->create([
            'name' => $request->name,
            'price' => $request->price,
            'current_price' => $request->price,
            'quantity' => $request->quantity,
            'exp_date' => $request->exp_date,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'owner_id' => Auth::id(),
            'img_url' => $image_url,
        ]);

        foreach ($request->discount_map as $item) {
            $product->discounts()->create([
                'percentage' => $item['percentage'],
                'number_of_days' => $item['number_of_days'],
            ]);
        }


        return response()->json($product, Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     ** @param Product $product
     * @return jsonResponse
     */
    public function show(Product $product): jsonResponse
    {
        $product->likes();
        $product->comments();
        $product->user();
        $product->category();
        $viewsCount = $product->views + 1;
        $product->update(['views' => $viewsCount]);

        return response()->json($product);
    }


    /**
     * Update the specified resource in storage.
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return jsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product): jsonResponse
    {
//        $uploadFolder = 'products';
//        $image = $request->file('product_image');
//        $image_uploaded_path = $image->store($uploadFolder, 'public');
//        $image_url = Storage::disk('public')->url($image_uploaded_path);

        //this Url changes up to the host
        $path = "http://localhost:8000/images/products/";
        $image_url = $path . '' . $this->savePhoto($request->product_image);

        $product->query()->update([
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'exp_date' => $request->exp_date,
            'img_url' => $image_url,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'owner_id' => Auth::id(),
        ]);

        foreach ($request->discount_map as $item) {
            $product->discounts()->update([
                'percentage' => $item['percentage'],
                'number_of_days' => $item['number_of_days'],
            ]);
        }


        return response()->json($product, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyProductRequest $request
     * @param Product $product
     * @return jsonResponse
     */
    public function destroy(DestroyProductRequest $request, Product $product): jsonResponse
    {
        $product->delete();
        return response()->json('product deleted successfully', Response::HTTP_OK);

    }

    public function search(Request $request): JsonResponse
    {
        if ($request->has('name')) {
            $result = Product::query()->where('name', 'LIKE', $request->name)->get();
            return response()->json($result, Response::HTTP_OK);
        }
        if ($request->has('category')) {
            $result = Product::query()->where('category_id', '=', $request->category)->get();
            return response()->json($result, Response::HTTP_OK);

        }
        if ($request->has('date')) {
            $result = Product::query()->where('exp_date', '=', $request->date)->get();
            return response()->json($result, Response::HTTP_OK);
        }
        if ($request->has('owner')) {
            $id = User::query()->where('name', 'like', $request->owner)->get();

            foreach ($id as $user) {
                $result = Product::query()->where('owner_id', '=', $user->id)->get();
            }

            return response()->json($result, Response::HTTP_OK);
        }


        return response()->json('BAD_REQUEST', Response::HTTP_BAD_REQUEST);


    }

}
