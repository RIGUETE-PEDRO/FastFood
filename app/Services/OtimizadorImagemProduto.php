<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class OtimizadorImagemProduto
{
    private const LARGURA_MAXIMA = 1200;
    private const ALTURA_MAXIMA = 1200;
    private const QUALIDADE_WEBP = 82;

    /**
     * Converte a imagem enviada para WebP e limita suas dimensões. O nome
     * retornado pode ser gravado diretamente em produtos.imagem_url.
     */
    public function salvar(UploadedFile $arquivo): string
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('A extensão GD com suporte a WebP não está habilitada no servidor.');
        }

        $origem = $arquivo->getRealPath();
        $metadados = $origem ? @getimagesize($origem) : false;

        if ($metadados === false) {
            throw new RuntimeException('O arquivo enviado não é uma imagem válida.');
        }

        $imagemOriginal = $this->abrirImagem($origem, $metadados['mime']);
        if ($imagemOriginal === false) {
            throw new RuntimeException('Esse formato de imagem não é suportado.');
        }

        [$largura, $altura] = $this->calcularDimensoes((int) $metadados[0], (int) $metadados[1]);
        $imagemOtimizada = imagecreatetruecolor($largura, $altura);

        imagealphablending($imagemOtimizada, false);
        imagesavealpha($imagemOtimizada, true);
        imagefill($imagemOtimizada, 0, 0, imagecolorallocatealpha($imagemOtimizada, 0, 0, 0, 127));
        imagecopyresampled($imagemOtimizada, $imagemOriginal, 0, 0, 0, 0, $largura, $altura, (int) $metadados[0], (int) $metadados[1]);

        $nome = uniqid('produto_', true) . '.webp';
        $destino = public_path('img/produtos/' . $nome);
        $salvou = imagewebp($imagemOtimizada, $destino, self::QUALIDADE_WEBP);

        imagedestroy($imagemOriginal);
        imagedestroy($imagemOtimizada);

        if (! $salvou) {
            throw new RuntimeException('Não foi possível salvar a imagem otimizada.');
        }

        return $nome;
    }

    private function abrirImagem(string $caminho, string $mime): \GdImage|false
    {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($caminho),
            'image/png' => imagecreatefrompng($caminho),
            'image/webp' => imagecreatefromwebp($caminho),
            default => false,
        };
    }

    private function calcularDimensoes(int $larguraOriginal, int $alturaOriginal): array
    {
        $escala = min(1, self::LARGURA_MAXIMA / $larguraOriginal, self::ALTURA_MAXIMA / $alturaOriginal);

        return [
            max(1, (int) round($larguraOriginal * $escala)),
            max(1, (int) round($alturaOriginal * $escala)),
        ];
    }
}
