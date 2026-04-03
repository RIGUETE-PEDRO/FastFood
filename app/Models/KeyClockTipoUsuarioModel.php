<?php

namespace App\Models;

use App\Enum\TipoUsuario;
use App\Roles\Role;
use Illuminate\Database\Eloquent\Model;

class KeyClockTipoUsuarioModel extends Model
{
    // Corrige para o nome real da tabela
    protected $table = 'keyclock_tipo_usuario';
    protected $fillable = [
        'tipo_usuario_id',
        'role_id',
    ];

    protected $guarded = [
        ''
    ];

    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function tipoUsuario()
    {
        return $this->belongsTo(TipoUsuario::class, 'tipo_usuario_id');
    }
}
