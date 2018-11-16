<?php

namespace App\Http\Controllers;

use Log;
use Mail;
use Hash;
use Validator;
use App\Models\User;
use App\Models\Loan;
use App\Models\Repayment;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    // create a new user account
    public function createUser(Request $request) {
        
        // validate
        $validator = Validator::make($request->all(), [
            'name' => 'required|present',
            'email' => 'required|unique:asp_users|present|email',
            'phone_number' => 'required|unique:asp_users|present|min:10|max:14',
            'password' => 'required|present|min:6',
            'bank_name' => 'required|present',
            'bank_branch' => 'required|present',
            'bank_account_number' => 'required|unique:asp_users|present|min:13|max:14',
            'bank_account_name' => 'required|present',
            'bank_account_phone_number' => 'required|unique:asp_users|present',
            'address' => 'required|present',
        ]);
        
        // return if errors exists
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        // inti class
        $user = new User;
        
        // set fields
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->password = Hash::make($request->password);
        $user->bank_name = $request->bank_name;
        $user->bank_branch = $request->bank_branch;
        $user->bank_account_number = $request->bank_account_number;
        $user->bank_account_name = $request->bank_account_name;
        $user->bank_account_phone_number = $request->bank_account_phone_number;
        $user->address = $request->address;
        
        // save and return appropriate response
        if($user->save()){
            return response()->json([
                'status' => 200,
                'message' => 'Welcome ' . $request->name . ', your account has been created successfully'
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Sorry ' . $request->name . ', something went wrong, please try again or contact support, thank you.'
            ]);
        }
    }
}
