<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoanCredited extends Mailable
{
    use Queueable, SerializesModels;
    
    protected $credit_amount_total;
    protected $duration;
    protected $repayment_frequency;
    protected $username;
    protected $bank;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($credit_amount_total,$duration,$repayment_frequency,$username,$bank)
    {
        $this->credit_amount_total = $credit_amount_total;
        $this->duration = $duration;
        $this->repayment_frequency = $repayment_frequency;
        $this->username = $username;
        $this->bank = $bank;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('loans@miniaspire.com')
                    ->markdown('emails.users.loan_credited')
                    ->with([
                        'credit_amount_total' => $this->credit_amount_total,
                        'duration' => $this->duration,
                        'repayment_frequency' => $this->repayment_frequency,
                        'username' => $this->username,
                        'bank' => $this->bank
                    ]);
    }
}
