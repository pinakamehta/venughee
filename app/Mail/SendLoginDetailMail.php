<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendLoginDetailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $address = env('MAIL_FROM_ADDRESS');
        $subject = 'Login Details.!';
        $name    = env('MAIL_FROM_NAME');

        return $this->view('emails.send_login_detail')
            ->from($address, $name)
            ->subject($subject)
            ->with([
                'username' => $this->data['username'],
                'password' => $this->data['password']
            ]);
    }
}
