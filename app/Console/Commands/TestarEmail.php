<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecuperarSenhaMail;

class TestarEmail extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Testa o envio de e-mail de recuperação de senha';

    public function handle()
    {
        $email = $this->argument('email');
        $token = 'TOKEN_DE_TESTE_123456';

        try {
            Mail::to($email)->send(new RecuperarSenhaMail($token, $email));
            $this->info("✅ E-mail enviado com sucesso para: {$email}");
            $this->info("Verifique sua caixa de entrada ou pasta de spam.");
        } catch (\Exception $e) {
            $this->error("❌ Erro ao enviar e-mail: " . $e->getMessage());
        }
    }
}
