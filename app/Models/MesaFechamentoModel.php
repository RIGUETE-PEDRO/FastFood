<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MesaFechamentoModel extends Model
{
    protected $table = 'mesa_fechamentos';

    protected $fillable = [
        'mesa_id',
        'numero_da_mesa',
        'total_pago',
        'total_itens',
        'formas_pagamento',
        'pagamentos_resumo',
        'fechado_em',
    ];

    protected $casts = [
        'formas_pagamento' => 'array',
        'pagamentos_resumo' => 'array',
        'fechado_em' => 'datetime',
    ];

    public function mesa()
    {
        return $this->belongsTo(MesaModel::class, 'mesa_id');
    }
}
