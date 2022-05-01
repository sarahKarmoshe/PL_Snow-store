<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Product;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\jsonResponse;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param Product $product
     * @return jsonResponse
     */
    public function IsLiked(Product $product): jsonResponse
    {
        $are_there_any_like = Like::query()->where('product_id', '=', $product->id)->exists();

        if ($are_there_any_like == false) {
            $product->likes()->create([
                'is_liked' => true,
                'owner_id' => Auth::id(),
            ]);
            $result = true;
        } else {
            $like = Like::query()->where('product_id', '=', $product->id)->get();
            $this_user_is_liked = false;
                foreach ($like as $item) {
                    if ($item->owner_id == Auth::id()) {
                        $this_user_is_liked = true;
                        $userLike = $item;
                    }
                }
                if ($this_user_is_liked == false) {
                    $product->likes()->create([
                        'is_liked' => true,
                        'owner_id' => Auth::id(),
                    ]);
                    $result = true;

                } else {
                    $userLike->delete();
                    $result=false;
                }

            }
        return response()->json($result,Response::HTTP_OK);
    }
}





/**
 * Remove the specified resource from storage.
 *
 * @param Like $like
 * @return jsonResponse
 * @throws AuthorizationException
 */
//    public function destroy(Product $product): jsonResponse
//    {
//        $like = Like::query()->where('product_id', '=', $product->id)->get();
//        foreach ($like as $item) {
//
//            if (Auth::id() != $item->owner_id) {
//                throw new AuthorizationException();
//            } else
//                $item->delete();
//        }
//        return response()->json(false, Response::HTTP_OK);
//
//    }
//}
