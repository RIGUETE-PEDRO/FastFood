<?php

namespace App\Repositoryimpl;

use App\Models\UsuarioModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LoginRepositoryimpl
{
    public function buscarUsuarioPorEmail(string $email): ?UsuarioModel
    {
        return UsuarioModel::where('email', $email)->first();
    }

    public function existeUsuarioPorEmail(string $email): bool
    {
        return UsuarioModel::where('email', $email)->exists();
    }

    public function deletarTokensRecuperacao(string $email): void
    {
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();
    }

    public function inserirTokenRecuperacao(string $email, string $token): void
    {
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);
    }

    public function buscarTokenRecuperacao(string $email, string $token): ?object
    {
        return DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();
    }

    public function atualizarSenhaUsuario(string $email, string $senhaHash): void
    {
        DB::table('usuarios')
            ->where('email', $email)
            ->update([
                'senha' => $senhaHash,
                'updated_at' => Carbon::now(),
            ]);
    }
}
