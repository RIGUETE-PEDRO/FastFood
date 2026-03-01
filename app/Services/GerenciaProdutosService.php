<?php

namespace App\Services;

use App\Models\Produto;


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
        }else{
            $produto->imagem_url = 'sem_imagem.jpg';
        }

        $produto->save();
        return $produto;
    }

    public function removerProduto($id)
    {

        $genericBase = new GenericBase();

        $usuarioLogado = $genericBase->pegarUsuarioLogado();

        if (($usuarioLogado['tipo'] ?? null) === 'Administrador') {

            $produto = Produto::find($id);

            if ($produto) {
                // Apagar imagem do produto
                if($produto->imagem_url && $produto->imagem_url !== 'sem_imagem.jpg'){
                    if ($produto->imagem_url && file_exists(public_path('img/produtos/' . $produto->imagem_url))) {
                        unlink(public_path('img/produtos/' . $produto->imagem_url));
                    }
                }
                $produto->delete();
            }
        }
    }

    public function atualizarProduto($id, $data)
    {
        $produto = Produto::find($id);
        if ($produto) {
            $produto->nome = $data['nome'];
            $produto->preco = str_replace(',', '.', $data['preco']);
            $produto->descricao = $data['descricao'];
            $produto->disponivel = $data['ativo'];
            $produto->categoria_id = $data['categoria_id'];

            // Se uma nova imagem foi enviada
            if (isset($data['imagem']) && $data['imagem']->isValid()) {
                // Apagar imagem antiga
                if($produto->imagem_url && $produto->imagem_url !== 'sem_imagem.jpg'){
                    if ($produto->imagem_url && file_exists(public_path('img/produtos/' . $produto->imagem_url))) {
                        unlink(public_path('img/produtos/' . $produto->imagem_url));
                    }
                }

                $file = $data['imagem'];
                $filename = uniqid('produto_') . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('img/produtos'), $filename);
                $produto->imagem_url = $filename;
            }

            $produto->save();
        }
    }

}
