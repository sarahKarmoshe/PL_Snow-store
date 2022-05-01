<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Passport\Token;

class RegisterController extends Controller
{


    public function savePhoto($photo)
    {
        $fileExtionsion = $photo->getClientOriginalExtension();
        $fileName = time() . '.' . $fileExtionsion;
        $path = 'images/users';
        $photo->move($path, $fileName);
        return $fileName;
    }


    public function signUp(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', Rule::unique('users')],
            'password' => ['required'],
            'c_password' => ['required', 'same:password'],
            'address' => ['required'],
            'phone' => ['required'],
            'user_image' => ['image:jpeg,png,jpg,gif,bmp', 'max:2048'],

        ]);

        if ($validator->fails()) {
            return Response()->json($validator->errors()->all(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $request['password'] = Hash::make($request['password']);


        $path="http://localhost:8000/images/users/";
        $image_url= $path.''.$this->savePhoto($request->user_image);

        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'c_password' => $request->password,
            'address' => $request->address,
            'phone' => $request->phone,
            'profile_img_url' => $image_url,
            'facebook_url' => $request->facebook_url,
            'whatsapp_url' => $request->whatsapp_url,

        ]);

        $verification_code = substr(number_format(  rand(), 0, '', ''), 0, 6);
        $user->sendApiEmailVerificationNotification($verification_code);

        $tokenResult = $user->createToken('personal Access Token')->accessToken;
        $data["user"] = $user;
        $data["verification_code"]=$verification_code;
        $data["tokenType"] = 'Bearer';
        $data["access_token"] = $tokenResult;

        return response()->json($data, Response::HTTP_OK);

    }

    public function verify(Request $request) {
        $userID = $request->id;
        $user = User::findOrFail($userID);
        $date = date("Y-m-d g:i:s");
        $user->email_verified_at = $date;
        $user->save();

        return response()->json("Email verified!" ,Response::HTTP_OK);
    }

    public function resend(Request $request){
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json("User already have verified email!", 422);
        }

        $verification_code = substr(number_format(  rand(), 0, '', ''), 0, 6);
        $request->user()->sendEmailVerificationNotification($verification_code);

        return response()->json("The notification has been resubmitted");

    }



    /**
     * @throws AuthenticationException
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->all(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            throw new AuthenticationException();
        }
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');

        $data["user"] = $user;
        $data["tokenType"] = 'Bearer';
        $data["access_token"] = $tokenResult->accessToken;

        return response()->json($data, Response::HTTP_OK);

    }

    public function logOut( $all = false)
    {

        Auth::user()->token()->revoke();

        if ($all) {
            $user= User::query()->where('id','=',Auth::id())->get();
            $user->oauth_access_tokens()->delete();
        }

        return response()->json("logged out", Response::HTTP_OK);

    }

    public function myProducts(): \Illuminate\Http\JsonResponse
    {
        $myProduct = Product::query()->where('owner_id', '=', Auth::id())->get();
        //delete the expired products
        $now = Carbon::now();
        foreach ($myProduct as $item) {
            $exp = Carbon::parse($item->exp_date);
            $still_valid = $now->diffInDays($exp, false);
            if ($still_valid <= 0) {
                {
                    $item->delete();
                }
            }
        }
        //get my own products
        $myProduct = Product::query()->where('owner_id', '=', Auth::id())->get();

        //calculate current price for my products
        foreach ($myProduct as $product) {
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

        return response()->json($myProduct, Response::HTTP_OK);

    }
    public function myProfile(){

        $user= User::query()->where('id','=',Auth::id())->get();
      return response()->json($user,Response::HTTP_OK);
    }
}
