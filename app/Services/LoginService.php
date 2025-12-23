<?php

namespace App\Services;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    //função de autenticação de usuário
        public function autenticar($credenciais)
        {
            $autenticou = false;
            // Busca o usuário pelo email
            $usuario = Usuario::where('email', $credenciais['email'])->first();

            if (!$usuario || !Hash::check($credenciais['senha'], $usuario->senha)) {

                 redirect()->back()->with('erro', 'Credenciais inválidas.');
                 return $autenticou = false;
            }

            if (!Usuario::where('email', $credenciais['email'])->exists()) {
                 redirect()->back()->with('erro', 'E-mail não cadastrado.');
                return $autenticou = false;
            }

            $autenticou = true;
            return $autenticou;

        }
}
