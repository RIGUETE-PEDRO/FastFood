<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Funcionario extends Model
{
    protected $table = 'funcionario';

    protected $fillable = [
        'usuario_id',
        'tipo_usuarios_id',
        'has_ativo',
        'salario'
    ];

    protected $casts = [
        'has_ativo' => 'boolean',
        'salario' => 'decimal:2'
    ];

    /**
     * Relacionamento com Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Accessor para pegar o nome do usuário
     */
    public function getNomeAttribute()
    {
        return $this->usuario?->nome;
    }

    /**
     * Accessor para pegar o email do usuário
     */
    public function getEmailAttribute()
    {
        return $this->usuario?->email;
    }

    /**
     * Accessor para pegar o telefone do usuário
     */
    public function getTelefoneAttribute()
    {
        return $this->usuario?->telefone;
    }

    /**
     * Accessor para pegar a foto do usuário
     */
    public function getFotoAttribute()
    {
        return $this->usuario?->url_imagem_perfil;
    }

    /**
     * Accessor para pegar a url da imagem de perfil
     */
    public function getUrlImagemPerfilAttribute()
    {
        return $this->usuario?->url_imagem_perfil;
    }

    /**
     * Accessor para pegar o tipo_usuario_id
     */
    public function getTipoUsuarioIdAttribute()
    {
        return $this->usuario?->tipo_usuario_id;
    }

    /**
     * Accessor para pegar a descrição do tipo
     */
    public function getTipoDescricaoAttribute()
    {
        return $this->usuario?->tipo_descricao;
    }
}
