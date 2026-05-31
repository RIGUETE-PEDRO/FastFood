<?php



namespace App\Repository;

interface LoginRepository
{
    public function buscarUsuarioPorEmail(string $email);

    public function existeUsuarioPorEmail(string $email);

    public function deletarTokensRecuperacao(string $email);

    public function inserirTokenRecuperacao(string $email, string $token);


    public function buscarTokenRecuperacao(string $email, string $token);


    public function atualizarSenhaUsuario(string $email, string $senhaHash);

}
