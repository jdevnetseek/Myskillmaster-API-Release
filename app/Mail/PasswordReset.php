<?php

namespace App\Mail;

use App\Models\PasswordReset as PasswordResetModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Instance of Password Reset
     *
     * @var \App\Models\PasswordReset
     */
    public $passwordReset;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(PasswordResetModel $passwordReset)
    {
        $this->passwordReset = $passwordReset->load('user');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.password.reset');
    }
}
