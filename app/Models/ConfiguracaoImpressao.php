<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoImpressao extends Model
{
    protected $table = 'configuracoes_impressao';

    protected $fillable = ['configuracoes'];

    protected $casts = [
        'configuracoes' => 'array',
    ];

    public static function atuais(): array
    {
        $registro = static::query()->firstOrCreate(
            ['id' => 1],
            ['configuracoes' => static::padrao()]
        );

        return array_merge(static::padrao(), $registro->configuracoes ?? []);
    }

    public static function salvar(array $configuracoes): void
    {
        static::query()->updateOrCreate(
            ['id' => 1],
            ['configuracoes' => array_merge(static::padrao(), $configuracoes)]
        );
    }

    private static function padrao(): array
    {
        return [
            'paperWidth' => '80',
            'fontSize' => '12',
            'receiptFont' => 'monospace',
            'copies' => 1,
            'padding' => 'normal',
            'autoPrintOnAccept' => false,
        ];
    }
}
