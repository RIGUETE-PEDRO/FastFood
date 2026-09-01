<?php

namespace App\Console\Commands;

use App\Models\ProdutoModel;
use App\Services\OtimizadorImagemProduto;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Throwable;

class OtimizarImagensProdutos extends Command
{
    protected $signature = 'produtos:otimizar-imagens';

    protected $description = 'Converte as imagens atuais dos produtos para WebP otimizado';

    public function handle(OtimizadorImagemProduto $otimizador): int
    {
        $otimizadas = 0;

        ProdutoModel::query()->whereNotNull('imagem_url')->orderBy('id')->each(function (ProdutoModel $produto) use ($otimizador, &$otimizadas) {
            if ($produto->imagem_url === 'sem_imagem.jpg') {
                return;
            }

            $caminhoAtual = public_path('img/produtos/' . $produto->imagem_url);
            if (! is_file($caminhoAtual)) {
                $this->warn("Imagem não encontrada para o produto #{$produto->id}: {$produto->imagem_url}");
                return;
            }

            try {
                $arquivo = new UploadedFile($caminhoAtual, basename($caminhoAtual), mime_content_type($caminhoAtual) ?: null, null, true);
                $novoNome = $otimizador->salvar($arquivo);
                $produto->update(['imagem_url' => $novoNome]);
                unlink($caminhoAtual);
                $otimizadas++;
            } catch (Throwable $erro) {
                $this->error("Produto #{$produto->id}: {$erro->getMessage()}");
            }
        });

        if ($otimizadas > 0) {
            Cache::forget('lista_produtos');
            Cache::forget('lista_produtos_destaque');
            Cache::forget('todos_produtos');
            Cache::forget('produtos_categorias');
        }

        $this->info("{$otimizadas} imagem(ns) otimizada(s).");

        return self::SUCCESS;
    }
}
