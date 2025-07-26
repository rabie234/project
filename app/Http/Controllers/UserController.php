<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUsername(Request $request)
    {
        return response()->json([
            'username' => $request->user()->username
        ]);
    }
}
