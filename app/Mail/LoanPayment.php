<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoanPayment extends Mailable
{
    use Queueable, SerializesModels;
    
    protected $new_remaining_balance;
    protected $debit_amount;
    protected $duration;
    protected $username;
    protected $bank;
    protected $loan_amount;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($new_remaining_balance,$debit_amount,$duration,$username,$bank,$loan_amount)
    {
        $this->new_remaining_balance = $new_remaining_balance;
        $this->debit_amount = $debit_amount;
        $this->duration = $duration;
        $this->username = $username;
        $this->bank = $bank;
        $this->loan_amount = $loan_amount;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('loans@miniaspire.com')
                    ->markdown('emails.users.loan_payment')
                    ->with([
                        'new_remaining_balance' => $this->new_remaining_balance,
                        'debit_amount' => $this->debit_amount,
                        'duration' => $this->duration,
                        'username' => $this->username,
                        'bank' => $this->bank,
                        'loan_amount' => $this->loan_amount,
                    ]);
    }
}
