<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MesaPagamentoModel extends Model
{
    protected $table = 'mesa_pagamentos';

    protected $fillable = [
        'mesa_id',
        'mesa_fechamento_id',
        'pagamento_metodo',
        'valor',
        'pago_em',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'pago_em' => 'datetime',
    ];

    public function mesa()
    {
        return $this->belongsTo(MesaModel::class, 'mesa_id');
    }

    public function fechamento()
    {
        return $this->belongsTo(MesaFechamentoModel::class, 'mesa_fechamento_id');
    }
}
