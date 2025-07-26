<?php

namespace App\Http\Controllers;

use App\Models\customerUser;
use Illuminate\Http\Request;

// use App\Models\supportCustomer;
// use Illuminate\Support\Facades\Hash;

class authentication extends Controller
{
    public function login(Request $request){
        $username = $request->input('username');
        $code = $request->input('password');
        $user = customerUser::where('user_name', $username)
        ->join('customer', 'customerUser.customer_id', '=', 'customer.id')
        ->where('customer.code', $code)
        ->select('customerUser.*','customer.*') // select columns as needed
        ->first();
       $response = null;
       if($user){
        $response = $user;
        $response['success'] = true;
       }else{
        $response['success'] = false;
       }
      
        // $response = supportCustomer::where('name_customer','=',$username)->where('code','=',$password)->first();
        // // $response = supportCustomer::all();
        // if($response){
        //     $response['success'] = true;
        // }else{
        //     $response['success'] = false;
        // }
       
        return response()->json($response);
    }
}
