<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use phpDocumentor\Reflection\Utils;
use Prophecy\Util\StringUtil;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->toJson()
            ], 400);
        }
        $data = $request->all();
        $user = new User();
        $user->fill($data);
        $user->save();
        return response()->json([
            'status' => 200,
            'data' => $user
        ]);
    }

    /*
     * the login function will attempt to sign the user and generate an authorization token if the user is found in the database
     * it will throw an error in case the the attempt fails and the user is not found
     * */
    public function login (Request $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->only('email', 'password');

        try {
            if (! $token = auth('api')->attempt($input)){
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid Credentials: email or password'
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Could not create token'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sign in Success',
            'token' => $token
        ]);
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->validate($request, [
                'token' => 'required'
            ]);
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => 'false',
                'message' => 'Sorry, the user cannot be logged out'
            ], 500);
        }


    }



}
