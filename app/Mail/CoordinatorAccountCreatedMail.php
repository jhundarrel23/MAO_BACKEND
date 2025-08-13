<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CoordinatorAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $coordinator;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct($coordinator, $password)
    {
        $this->coordinator = $coordinator;
        $this->password = $password;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Coordinator Account Details')
                    ->view('emails.coordinator_account_created')
                    ->with([
                        'name' => $this->coordinator->fname . ' ' . $this->coordinator->lname,
                        'username' => $this->coordinator->username,
                        'email' => $this->coordinator->email,
                        'password' => $this->password,
                    ]);
    }
}
