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

    public static function formatosBobina(): array
    {
        return [
            '57x30' => ['label' => 'POS portátil — 57 mm × 30 mm', 'width' => 57, 'diameter' => 30],
            '57x40' => ['label' => 'POS portátil — 57 mm × 40 mm', 'width' => 57, 'diameter' => 40],
            '80x40' => ['label' => 'PDV — 80 mm × 40 mm', 'width' => 80, 'diameter' => 40],
            '80x60' => ['label' => 'PDV — 80 mm × 60 mm', 'width' => 80, 'diameter' => 60],
            '80x80' => ['label' => 'PDV — 80 mm × 80 mm', 'width' => 80, 'diameter' => 80],
        ];
    }

    public static function atuais(): array
    {
        $registro = static::query()->firstOrCreate(
            ['id' => 1],
            ['configuracoes' => static::padrao()]
        );

        $salvas = $registro->configuracoes ?? [];
        $atuais = array_merge(static::padrao(), $salvas);

        // Converte opções antigas que usavam a metragem do rolo no identificador.
        $formatosAntigos = [
            '57x15' => '57x30',
            '57x22' => '57x40',
            '80x30' => '80x40',
        ];
        if (isset($formatosAntigos[$atuais['paperRoll'] ?? ''])) {
            $atuais['paperRoll'] = $formatosAntigos[$atuais['paperRoll']];
        }

        // Converte preferências antigas que salvavam apenas a largura.
        if (!array_key_exists('paperRoll', $salvas) && isset($salvas['paperWidth'])) {
            $atuais['paperRoll'] = (string) $salvas['paperWidth'] === '58' ? '57x30' : '80x40';
        }

        return $atuais;
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
            'paperRoll' => '80x40',
            'fontSize' => '12',
            'receiptFont' => 'monospace',
            'copies' => 1,
            'padding' => 'normal',
            'autoPrintOnAccept' => false,
        ];
    }
}
