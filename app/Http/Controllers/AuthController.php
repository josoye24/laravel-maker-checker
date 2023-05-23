<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class AuthController extends Controller
{
    public function createNewAdmin(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'admin_role' => 'required|in:support,supervisor,superadmin',
            'password' => 'required|min:6',
        ]);

        $authenticatedUser = Auth::guard('api')->user();

        // Check that only super admin can add a new admin user
        $admin = User::where('id', $authenticatedUser->id)
            ->where('admin_role', 'superadmin')
            ->first();


        if (!$admin) {
            return response()->api(null, false, 'Only super admin can create a new admin user', 200);
        }

        // Create a new admin user
        $user = User::create([
            'email' => $validatedData['email'],
            'name' => $validatedData['name'],
            'admin_role' => $validatedData['admin_role'],
            'added_by' => $authenticatedUser->id,
            'password' => bcrypt($validatedData['password']),
        ]);

        return response()->api($user, true, 'New Admin user created successfully', 200);
    }

    public function login(Request $request)
    {
        // Validate input
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to log in
        if (!Auth::attempt($validatedData)) {
            return response()->api(null, false, 'Invalid credentials.', 401);
        }

        // Generate API token
        $user = User::where('email', $validatedData['email'])->first();
        $token = $user->createToken('API Token')->accessToken;

        $data = [
            'token' => $token,
            'user' => $user
        ];

        return response()->api($data, true, 'Login Successful', 200);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->token()->revoke();

        return response()->api(null, true, 'Logged out successfully', 200);
    }

    public function getAdminUser(Request $request)
    {
        $user = User::where('id', $request->id)->first();

        return response()->api($user, true, 'Admin user retrieved successfully', 200);
    }

    public function getAllAdminUsers(Request $request)
    {
        $users = User::leftJoin('users as added_by_user', 'users.added_by', '=', 'added_by_user.id')
            ->select('users.*', 'added_by_user.name as added_by_name')
            ->get();

        return response()->api($users, true, 'All Admin users retrieved successfully', 200);
    }
    public function updateAdminRole(Request $request)
    {
        $validatedData = $request->validate([
            'admin_id' => 'required',
            'admin_role' => 'required|in:support,supervisor,superadmin',
        ]);

        // check that support admin cannot update admin role
        $authenticatedUser = Auth::guard('api')->user();
        $isSupport = $authenticatedUser->admin_role === 'support';

        if ($isSupport) {
            return response()->api(null, false, 'Support admin user cannot update admin role', 200);
        }

        // check that admin user exists
        $admin = User::where('id', $validatedData['admin_id'])->first();
        if (!$admin) {
            return response()->api(null, false, 'Admin user does not exist', 200);
        }

        $admin->admin_role = $validatedData['admin_role'];
        $admin->save();

        return response()->api($admin, true, 'Admin user role updated successfully', 200);
    }
}
