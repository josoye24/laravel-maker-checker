<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserProfile;

class UserProfileController extends Controller
{
    public function index()
    {
        $users =  UserProfile::all();
        return response()->api($users, true, "Users Profile retrieved successfully", 200);
    }
}
