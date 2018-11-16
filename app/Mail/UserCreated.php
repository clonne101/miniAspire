<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserCreated extends Mailable
{
    use Queueable, SerializesModels;
    
    protected $createdUser;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($createdUser)
    {
        $this->createdUser = $createdUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('example@example.com')
                    ->markdown('emails.users.create')
                    ->with([
                        'name' => $this->createdUser->name,
                    ]);
    }
}
