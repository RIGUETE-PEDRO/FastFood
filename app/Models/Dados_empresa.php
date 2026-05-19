<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dados_empresa extends Model
{
    protected $table = 'dados_empresa';
    protected $fillable = [
        'Valor',
        'informacoes',
    ];

}
