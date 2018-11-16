<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OutstandingLoan extends Mailable
{
    use Queueable, SerializesModels;
    
    protected $remaining_balance;
    protected $duration;
    protected $repayment_frequency;
    protected $username;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($remaining_balance,$duration,$repayment_frequency,$username)
    {
        $this->remaining_balance = $remaining_balance;
        $this->duration = $duration;
        $this->repayment_frequency = $repayment_frequency;
        $this->username = $username;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('loans@miniaspire.com')
                    ->markdown('emails.users.outstanding_loan')
                    ->with([
                        'remaining_balance' => $this->remaining_balance,
                        'duration' => $this->duration,
                        'repayment_frequency' => $this->repayment_frequency,
                        'username' => $this->username
                    ]);
    }
}
