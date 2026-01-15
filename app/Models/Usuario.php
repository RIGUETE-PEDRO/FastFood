<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'telefone',
        'tipo_usuario_id',
        'url_imagem_perfil'
    ];

    protected $hidden = [
        'senha',
    ];

    public function getAuthPassword()
    {
        return $this->senha;
    }

    /**
     * Relacionamento com Funcionario (um usuário pode ser um funcionário)
     */
    public function funcionario(): HasOne
    {
        return $this->hasOne(Funcionario::class, 'usuario_id');
    }

    /**
     * Verifica se o usuário é um funcionário
     */
    public function isFuncionario(): bool
    {
        return $this->funcionario()->exists();
    }

    /**
     * Retorna o salário do funcionário (se for funcionário)
     */
    public function getSalarioAttribute()
    {
        return $this->funcionario?->salario;
    }

    /**
     * Verifica se o usuário está ativo (para funcionários)
     */
    public function isAtivo(): bool
    {
        if ($this->isFuncionario()) {
            return $this->funcionario->has_ativo;
        }
        return true; // Se não for funcionário, considera ativo
    }

    /**
     * Retorna a descrição do tipo de usuário
     */
    public function getTipoDescricaoAttribute(): string
    {
        return match ($this->tipo_usuario_id) {
            1 => 'Cliente',
            2 => 'Estabelecimento',
            3 => 'Administrador',
            4 => 'Entregador',
            default => 'Outro',
        };
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }
}
