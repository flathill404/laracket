<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Queries\GetUserProfile;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request, GetUserProfile $query)
    {
        return $query->execute($request->user());
    }
}
