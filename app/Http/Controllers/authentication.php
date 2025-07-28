<?php

namespace App\Http\Controllers;

use App\Models\customerUser;
use App\Models\customers;
use Illuminate\Http\Request;

// use App\Models\supportCustomer;
// use Illuminate\Support\Facades\Hash;

class authentication extends Controller
{
    public function login(Request $request){
        
        $username = $request->input('username');
        $code = $request->input('password');

       
        // Query with correct table name 'customer' (not 'customers')
        $user = customerUser::join('customer', 'customerUser.customer_id', '=', 'customer.id')
            ->where('customerUser.user_name',$username)
            ->where('customer.code', $code)
            ->select('customerUser.*', 'customer.*')
            ->first();

        $response = [];
        if ($user) {
            $response = $user->toArray();
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['message'] = 'Invalid username or password';
        }
        return response()->json($response);
    }
}
