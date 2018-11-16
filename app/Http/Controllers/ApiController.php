<?php

namespace App\Http\Controllers;

use Log;
use Mail;
use Hash;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Loan;
use App\Models\Repayment;
use App\Mail\UserCreated;
use App\Mail\LoanPayment;
use App\Mail\LoanCredited;
use Illuminate\Http\Request;
use App\Mail\OutstandingLoan;

class ApiController extends Controller
{
    protected $interest_rate = 12.5; // percentage
    protected $arrangement_fee = 100.5; // cash
    protected $duration = '6 months'; // carbon date
    
    // init contructor
    public function __costruct($interest_rate, $arrangement_fee, $duration) {
        $this->interest_rate = $interest_rate;
        $this->arrangement_fee = $arrangement_fee;
        $this->duration = $duration;
    }
    
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
        $api_token = str_random(20);
        
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
            $api_token = str_random(20);
            
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
            'repayment_frequency' => 'required|present',
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
            $credit_amount_total = round($interest_total, 2) + round($outstandingLoan->arrangement_fee, 2) + round($outstandingLoan->credit_amount, 2);
            
            // check if any repayments exists
            $repayments = Repayment::where('loan_id', $outstandingLoan->id)->where('status', 'SUCCESSFUL')->first();
            
            // remaining balance
            if($repayments) {
                $remaining_balance = $repayments->remaining_balance;
            } else {
                $remaining_balance = $credit_amount_total;
            }
            
            // payment duration and frequency
            $duration = Carbon::parse($outstandingLoan->duration)->diffForHumans();
            $repayment_frequency = $outstandingLoan->repayment_frequency;
            $username = $user->name;
            
            // send mail to inform user
            Mail::to($user->email)->send(new OutstandingLoan($remaining_balance,$duration,$repayment_frequency,$username));
            
            // respond
            return response()->json([
                'status' => 400,
                'message' => 'Hello ' . $user->name . ', please do well to pay your outstanding loan of $' . $remaining_balance . ' in ' . $duration . ' or contact support if it is critical, thank you.'
            ]);
            
        } else {
            
            // GO AHEAD AND GRANT LOAN
            
            // find the interest rate of the credit amount
            $interest_total = round($request->credit_amount, 2) / round($this->interest_rate, 2);
            
            // calculate outstanding loan to be paid
            $credit_amount_total = round($interest_total, 2) + round($this->arrangement_fee, 2) + round($request->credit_amount, 2);
            
            // calculate duration
            $final_duration = Carbon::parse($this->duration)->format('Y-m-d');
            
            // check repayment frequency
            $rep_freq = array('monthly', 'weekly');
            if (!in_array($request->repayment_frequency,$rep_freq)) {
                return response()->json([
                    'status' => 400,
                    'message' => "Please enter either 'monthly' or 'weekly' as your repayment frequency to continue, thank you."
                ]);
            }
            
            // init loan class
            $loan = new Loan;
            
            // set values
            $loan->user_id = $user->id;
            $loan->duration = $final_duration;
            $loan->repayment_frequency = strtoupper($request->repayment_frequency);
            $loan->interest_rate = $this->interest_rate;
            $loan->arrangement_fee = $this->arrangement_fee;
            $loan->credit_amount = round($request->credit_amount, 2);
            $loan->status = 'CREDITED';
            
            // now save
            if ($loan->save()) {
                
                // AFTER USER's BANK ACCOUNT HAS BEEN CREDITED WITH THE REQUESTED AMOUNT
                
                // set mail vars
                $duration = [
                    'humans' => Carbon::parse($final_duration)->diffForHumans(),
                    'date' => $final_duration
                ];
                $repayment_frequency = strtoupper($request->repayment_frequency);
                $username = $user->name;
                $bank = [
                   'bank_name' => $user->bank_name,
                   'bank_account' => $user->bank_account_number
                ];
                
                // send mail
                Mail::to($user->email)->send(new LoanCredited($credit_amount_total,$duration,$repayment_frequency,$username,$bank));
                
                // create repayment instance
                $repay = new Repayment;
                $repay->loan_id = $loan->id;
                $repay->amount_paid = 0.00;
                $repay->remaining_balance = round($credit_amount_total, 2);
                $repay->status = 'SUCCESSFUL';
                
                // now save
                if($repay->save()){
                    // respond
                    return response()->json([
                        'status' => 200,
                        'message' => 'Great! ' . $user->name . ', your loan has been processed successfully, please do well to check your email, thank you.'
                    ]);
                }
                
            }
            
        }
    }
    
    // user loan repay
    public function userRepayLoan(Request $request) {
        
        // validate
        $validator = Validator::make($request->all(), [
            'api_token' => 'required|present',
            'debit_amount' => 'required|present',
        ]);
        
        // return if errors exists
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        // find user
        $user = User::where('api_token', $request->api_token)->first();
        if(!$user) {
            return response()->json([
                'status' => 400,
                'message' => 'Sorry no user exists with your details, please create an account'
            ]);
        }
        
        // find loan
        $loan = Loan::where('user_id', $user->id)->where('status', 'CREDITED')->orWhere('status', 'PARTIAL_PAYMENT')->first();
        if(!$loan) {
            return response()->json([
                'status' => 400,
                'message' => 'Hello ' . $user->name . ', you have no outstanding loans, please contact support if you have any difficulties, thank you.'
            ]);
        }
        
        // find repayment data
        $repayment = Repayment::where('loan_id', $loan->id)->where('status', '!=', 'CLOSED')->first();
        
        // compare amounts
        $remaining_balance = round($repayment->remaining_balance, 2);
        $debit_amount = round($request->debit_amount, 2);
        $amount_paid = round($repayment->amount_paid, 2);
        
        if ( $debit_amount > $remaining_balance) {
            // respond
            return response()->json([
                'status' => 400,
                'message' => 'Hello ' . $user->name . ', please enter $' . $remaining_balance . ' as the debit amount instead of the amount specified $' . $debit_amount
            ]);
        }
        
        // go ahead and debit
        $new_remaining_balance = $remaining_balance - $debit_amount;
        
        // set status
        if($new_remaining_balance > 0) {
            $repayment->status = 'SUCCESSFUL';
            $loan->status = 'PARTIAL_PAYMENT';
            
            $repayment->amount_paid = $amount_paid + $debit_amount;
            $repayment->remaining_balance = $new_remaining_balance;
        } else {
            $repayment->status = 'CLOSED';
            $loan->status = 'PAID';
            
            $repayment->amount_paid = 0.00;
            $repayment->remaining_balance = $new_remaining_balance;
        }
        
        // send mail [also mimics bank debit]
        // set mail vars
        $duration = [
            'humans' => Carbon::parse($loan->duration)->diffForHumans(),
            'date' => $loan->duration
        ];
        $username = $user->name;
        $bank = [
           'bank_name' => $user->bank_name,
           'bank_account' => $user->bank_account_number
        ];
        
        // find the interest rate of the credit amount
        $interest_total = round($loan->credit_amount, 2) / round($this->interest_rate, 2);
        // calculate outstanding loan to be paid
        $credit_amount_total = round($interest_total, 2) + round($this->arrangement_fee, 2) + round($loan->credit_amount, 2);
        // set to var
        $loan_amount = $credit_amount_total;
        
        // send mail
        Mail::to($user->email)->send(new LoanPayment($new_remaining_balance,$debit_amount,$duration,$username,$bank,$loan_amount));
        
        // now save
        if ($repayment->save() && $loan->save()) {
            // respond
            return response()->json([
                'status' => 200,
                'message' => 'Hello ' . $user->name . ', thank your for paying $' . $debit_amount . ' of your $' . $loan_amount . ' loan, you now have an outstanding balance of $' . $new_remaining_balance
            ]); 
        }
        
    }
    
    // user loan status
    public function userLoanStatus(Request $request) {
        
        // validate
        $validator = Validator::make($request->all(), [
            'api_token' => 'required|present',
        ]);
        
        // return if errors exists
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        // find user
        $user = User::where('api_token', $request->api_token)->first();
        if(!$user) {
            return response()->json([
                'status' => 400,
                'message' => 'Sorry no user exists with your details, please create an account'
            ]);
        }
        
        // find loan
        $loan = Loan::where('user_id', $user->id)->where('status', 'CREDITED')->orWhere('status', 'PARTIAL_PAYMENT')->first();
        if(!$loan) {
            return response()->json([
                'status' => 400,
                'message' => 'Hello ' . $user->name . ', you have no outstanding loans, please contact support if you have any difficulties, thank you.'
            ]);
        }
        
        // find repayment data
        $repayment = Repayment::where('loan_id', $loan->id)->where('status', '!=', 'CLOSED')->first();
        
        // find the interest rate of the credit amount
        $interest_total = round($loan->credit_amount, 2) / round($loan->interest_rate, 2);
        
        // prepare response data
        $res_data = [
            'loan' => [
                'credit_amount' => '$' . $loan->credit_amount,
                'duration' => [
                    'date' => $loan->duration,
                    'humans' => Carbon::parse($loan->duration)->diffForHumans()
                ],
                'repayment_frequency' => $loan->repayment_frequency,
                'interest_rate' => [
                    'percentage' => $loan->interest_rate,
                    'amount' => '$' . $interest_total
                ],
                'arrangement_fee' => '$' . $loan->arrangement_fee,
                'status' => $loan->status,
                'requested_on' => [
                    'date' => $loan->created_at,
                    'humans' => Carbon::parse($loan->created_at)->diffForHumans()
                ],
            ],
            'repayment' => [
                'amount_paid' => '$' . $repayment->amount_paid,
                'remaining_balance' => '$' . $repayment->remaining_balance,
                'status' => $repayment->status,
                'updated_on' => [
                    'date' => $repayment->updated_at,
                    'humans' => Carbon::parse($repayment->updated_at)->diffForHumans()
                ],
            ],
            'user_bank_info' => [
                'bank_name' => $user->bank_name,
                'bank_branch' => $user->bank_branch,
                'bank_account_number' => $user->bank_account_number,
                'bank_account_name' => $user->bank_account_name,
                'bank_account_phone_number' => $user->bank_account_phone_number
            ],
        ];
        
        // respond
        return response()->json([
            'status' => 200,
            'message' => 'Hello ' . $user->name . ', your loan data request completed successfully',
            'data' => $res_data
        ]);
        
    }
}
