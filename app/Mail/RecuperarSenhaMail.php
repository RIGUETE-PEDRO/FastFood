<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecuperarSenhaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        $url = url('/redefinir-senha?token=' . $this->token . '&email=' . $this->email);

        return $this->subject('Recuperação de Senha - FlashFood')
                    ->view('emails.recuperar-senha')
                    ->with([
                        'url' => $url,
                        'token' => $this->token,
                    ]);
    }
}
