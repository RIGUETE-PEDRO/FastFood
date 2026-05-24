<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolesModel extends Model
{
    // Corrige para o nome real da tabela
    protected $table = 'roles';
    protected $fillable = [
        'nome',
    ];

    protected $guarded = [
        'deleted'
    ];

    public function SecureKeyTipoUsuarios()
    {
        return $this->hasMany(SecureKeyTipoUsuarioModel::class, 'role_id');
    }
}
