<?php

use App\Models\UsuarioModel;
use App\Roles\Roles;
use App\Services\SecureKeyService;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('pedidos.admin', function (UsuarioModel $user) {
    return app(SecureKeyService::class)->hasRole($user, Roles::PEDIDOS);
});
