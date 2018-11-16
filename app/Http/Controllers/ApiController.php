<?php

namespace App\Http\Controllers;

use Log;
use Mail;
use Hash;
use Validator;
use App\Models\User;
use App\Models\Loan;
use App\Models\Repayment;
use App\Mail\UserCreated;
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
        
        // generate API token
        $hash_param = $request->name . ':' . $request->phone_number . ':' . $request->email;
        $api_token = Hash::make($hash_param, ['rounds' => 12]);
        
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
        $user->api_token = $api_token;
        
        // save and return appropriate response
        if($user->save()){
            
            // get user
            $createdUser = User::where('email', $request->email)->first();
            
            // send mail before responding
            Mail::to($request->email)->send(new UserCreated($createdUser));
            
            // respond
            return response()->json([
                'status' => 200,
                'message' => 'Welcome ' . $request->name . ', your account has been created and please do well to check your email, thank you.'
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Sorry ' . $request->name . ', something went wrong, please try again or contact support, thank you.'
            ]);
        }
    }
    
    // user account login
    public function userLogin(Request $request) {
        
        // validate
        $validator = Validator::make($request->all(), [
            'email' => 'required|present|email',
            'password' => 'required|present',
        ]);
        
        // return if errors exists
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        // check if user exists
        $user = User::where('email', $request->email)->first();
        if(!$user) {
            return response()->json([
                'status' => 400,
                'message' => 'Sorry no user exists with your details, please create an account'
            ]);
        }
        
        // check password
        if(Hash::check($request->password, $user->password)) {
            
            // re-generate API token
            $hash_param = $request->name . ':' . $request->phone_number . ':' . $request->email;
            $api_token = Hash::make($hash_param, ['rounds' => 12]);
            
            // save to user details
            $user->api_token = $api_token;
            
            if ($user->save()){
                // response
                return response()->json([
                    'status' => 200,
                    'message' => 'Hello ' . $user->name . ', welcome back!',
                    'api_token' => $api_token
                ]);
            }
            
        } else {
            
            // response
            return response()->json([
                'status' => 400,
                'message' => 'Sorry, your credentials do not match our records, please try again or contact support, thank you.'
            ]);
            
        }
        
    }
    
    // user loan request
    public function userLoan(Request $request) {
        
        // validate
        $validator = Validator::make($request->all(), [
            'api_token' => 'required|present',
            'credit_amount' => 'required|present',
        ]);
        
        // return if errors exists
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        // check if user exists
        $user = User::where('api_token', $request->api_token)->first();
        if(!$user) {
            return response()->json([
                'status' => 400,
                'message' => 'Sorry no user exists with your details, please create an account'
            ]);
        }
        
        // check if user has any outstanding loans
        $outstandingLoan = Loan::where('status', '!=', 'PAID')->first();
        if($outstandingLoan) {
            // init total var
            $remaining_balance;
            
            // find the interest rate of the credit amount
            $interest_total = round($outstandingLoan->credit_amount, 2) / round($outstandingLoan->interest_rate, 2);
            
            // calculate outstanding loan to be paid
            $credit_amount_total = round($interest_total, 2) + round($outstandingLoan->arrangement_fee, 2) + round($outstandingLoan->credit_amount);
            
            // check if any repayments exists
            $repayments = Repayment::where('loan_id', $outstandingLoan->id)->where('status', 'SUCCESSFUL')->first();
            
            // remaining balance
            if($repayments) {
                $remaining_balance = $repayments->remaining_balance;
            } else {
                $remaining_balance = $credit_amount_total;
            }
            
            // respond
            return response()->json([
                'status' => 400,
                'message' => 'Hello ' . $user->name . ', please do well to pay your outstanding loan of $' . $remaining_balance . ' or contact support if it is critical, thank you.'
            ]);
        }
    }
}
