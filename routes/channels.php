<?php

use App\Models\UsuarioModel;
use App\Roles\Roles;
use App\Services\SecureKeyService;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('pedidos.admin', function (UsuarioModel $user) {
    $secureKey = app(SecureKeyService::class);

    return $secureKey->hasRole($user, Roles::PEDIDOS)
        || $secureKey->hasRole($user, Roles::PEDIDOS_VISUALIZAR);
});
