<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\jsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{


    public function index(Product $product)
    {

        $comments = $product->comments()->get();
        foreach ($comments as $comment)
        {

            $user=User::query()->where('id','=',$comment->owner_id)->get();
            $comment->user=$user;
        }
        return response()->json($comments, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Product $product
     * @param Request $request
     * @return jsonResponse
     */
    public function store(Product $product, Request $request): jsonResponse
    {
        $comment = $product->comments()->create([
            'comment' => $request->comment,
            'owner_id' => Auth::id(),
        ]);
        return response()->json($comment, Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     * @param Comment $comment
     * @return jsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Comment $comment): jsonResponse
    {
        if (Auth::id() == $comment->owner_id) {
            $comment->delete();
            return response()->json('comment deleted successfully', Response::HTTP_OK);

        }
        throw new AuthorizationException();
    }


}
