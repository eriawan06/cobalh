<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
// use Dotenv\Exception\ValidationException;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller 
{
    public function register(Request $request)
    {
        $validationRules = [
            'fullname' => 'required|string',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'phone' => 'required|numeric|unique:users',
            'role' => 'required|in:superadmin,admin,user',
            'gender' => 'required|in:male,female',
            'birth_date' => 'date_format:Y-m-d',
            'password' => 'required|confirmed',
        ];

        $validator = Validator::make($request->input(), $validationRules);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }

        $user = new User();
        $user->fullname = $request->fullname;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = $request->role;
        $user->gender = $request->gender;
        $user->birth_date = $request->birth_date;
        $user->password = app('hash')->make($request->password);
        $user->save();

        return response()->json([
            'is_success' => true,
            'data' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $input = $request->all();

        $validationRules = [
            'email' => 'required|email',
            'password' => 'required|string',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
        
        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            throw new AuthorizationException('Wrong email or password', 401);
        }

        return response()->json([
            'is_success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => Auth::factory()->getTTL() * 60,
            ],
        ], 200);
    }
}

?>