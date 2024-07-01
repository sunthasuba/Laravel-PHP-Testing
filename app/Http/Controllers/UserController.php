<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        Log::info('UserController@index called');
        $users = User::all();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        Log::info('UserController@store called');

        $usersData = $request->all();
        $createdUsers = [];

        foreach ($usersData as $userData) {
            $validator = \Validator::make($userData, [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|in:admin,user'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $user = User::create($userData);
            $createdUsers[] = $user;
        }

        return response()->json([
            'message' => 'Users created successfully',
            'users' => $createdUsers
        ], 201);  // 201 Created
    }

    public function show(User $user)
    {
        Log::info('UserController@show called');
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        Log::info('UserController@update called');

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user'
        ]);

        $user->update($request->all());

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ], 200); 
    }

    public function destroy(User $user)
    {
        Log::info('UserController@destroy called');
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
