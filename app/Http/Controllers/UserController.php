<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10)->toArray();
        return response()->json([
            'is_success' => true,
            'data' => $users['data'],
            'total_count' => $users['total'],
            'pagination' => [
                'next_page' => $users['next_page_url'],
                'current_page' => $users['current_page'],
            ],
        ], 200);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            throw new NotFoundHttpException('No data available');
        }

        $authenticatedUser = Auth::user();
        if ($authenticatedUser->role == 'user') {
            if ($authenticatedUser->id != $id) {
                throw new BadRequestException();
            }
        }

        return response()->json([
            'is_success' => true,
            'data' => $user,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            throw new NotFoundHttpException('No data available');
        }

        $authenticatedUser = Auth::user();
        if ($authenticatedUser->role == 'user') {
            if ($authenticatedUser->id != $id) {
                throw new BadRequestException();
            }
        }

        $input = $request->except('password');
        $validationRules = [
            'fullname' => 'required|string',
            'username' => 'required|string|unique:users,username,'.$user->id,
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'required|numeric|unique:users,phone,'.$user->id,
            'role' => 'required|in:superadmin,admin,user',
            'gender' => 'required|in:male,female',
            'birth_date' => 'date_format:Y-m-d',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }

        $user->fill($input);
        $user->save();

        return response()->json([
            'is_success' => true,
            'data' => $user,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            throw new NotFoundHttpException('No data available');
        }
        $user->delete();
        return response()->json(['is_success' => true], 200);
    }
}
