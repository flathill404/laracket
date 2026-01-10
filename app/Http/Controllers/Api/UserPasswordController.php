<?php

namespace App\Http\Controllers\Api;

use App\Actions\User\UpdateUserPassword;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserPasswordController extends Controller
{
    public function update(Request $request, UpdateUserPassword $action)
    {
        $action($request->user(), $request->input('password'));

        return response()->noContent();
    }
}
