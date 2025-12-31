<?php

namespace App\Services;

use App\Models\Produto;
use Illuminate\Support\Facades\Log;

class GerenciaProdutosService
{
    public function criarProduto($request)
    {
        $preco = str_replace(',', '.', $request->preco);
        $produto = new Produto(
            [
                'nome' => $request->input('nome'),
                'preco' => $preco,
                'descricao' => $request->input('descricao'),
                'imagem_url' => $request->input('imagem'),
                'disponivel' => $request->input('ativo'),
                'categoria_id' => $request->input('categoria_id'),
            ]
        );

        if ($request->hasFile('imagem')) {
            $file = $request->file('imagem');
            $filename = uniqid('produto_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/produtos'), $filename);
            $produto->imagem_url = $filename;
        }

        $produto->save();
        return $produto;
    }

    public function removerProduto($id)
    {
Log::info('Mensagem de teste');
        $genericBase = new GenericBase();
        if ($genericBase->pegarUsuarioLogado()['tipo'] == 'Administrador') {

            $produto = Produto::find($id);
            if ($produto) {
                // Apagar imagem do produto
                if ($produto->imagem_url && file_exists(public_path('img/produtos/' . $produto->imagem_url))) {
                    unlink(public_path('img/produtos/' . $produto->imagem_url));
                }
                $produto->delete();
            }
        }else{
            throw new \Exception("Ação não autorizada. Apenas administradores podem deletar produtos.");
        }

    }
}
