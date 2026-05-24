<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeyClockAuditoriaModel extends Model
{
    protected $table = 'key_clock_auditoria';

    protected $fillable = [
        'usuario_id',
        'acao',
        'recurso',
        'detalhes',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'detalhes' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }

    /**
     * Escopo: últimas auditorias
     */
    public function scopeRecentes($query, $limite = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limite);
    }

    /**
     * Escopo: filtrar por ação
     */
    public function scopePorAcao($query, $acao)
    {
        return $query->where('acao', $acao);
    }

    /**
     * Escopo: filtrar por usuário
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }
}
