<?php

namespace App\Http\Controllers;

use App\Models\ProdutoModel;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;

/**
 * Exemplo de Controller com Auditoria Integrada
 *
 * Este controller demonstra como adicionar registros de auditoria
 * em operações CRUD de produtos.
 */
class ProdutoComAuditoriaController extends Controller
{
    /**
     * Listar produtos (auditoria via middleware em GET importante)
     */
    public function index()
    {
        // Registra visualização em lote
        AuditoriaService::registrarVisualizacao('produtos', 0);

        return view('produtos.index', [
            'produtos' => ProdutoModel::paginate(15),
        ]);
    }

    /**
     * Mostrar produto específico
     */
    public function show($id)
    {
        $produto = ProdutoModel::findOrFail($id);

        // Registra visualização individual
        AuditoriaService::registrarVisualizacao('produto', $id);

        return view('produtos.show', ['produto' => $produto]);
    }

    /**
     * Armazenar novo produto
     */
    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'required|string',
            'preco' => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:categoria_produtos,id',
        ]);

        $produto = ProdutoModel::create($dados);

        // Registra criação com detalhes
        AuditoriaService::registrarCriacao('produto', [
            'id' => $produto->id,
            'nome' => $produto->nome,
            'preco' => $produto->preco,
            'categoria_id' => $produto->categoria_id,
        ]);

        return redirect()->route('produtos.show', $produto)
            ->with('success', 'Produto criado com sucesso!');
    }

    /**
     * Atualizar produto
     */
    public function update(Request $request, $id)
    {
        $produto = ProdutoModel::findOrFail($id);
        $dadosAntigos = $produto->only(['nome', 'preco', 'descricao', 'categoria_id']);

        $dadosNovos = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'required|string',
            'preco' => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:categoria_produtos,id',
        ]);

        $produto->update($dadosNovos);

        // Registra atualização com campos alterados
        AuditoriaService::registrarAtualizacao(
            'produto',
            $dadosAntigos,
            $dadosNovos
        );

        return redirect()->route('produtos.show', $produto)
            ->with('success', 'Produto atualizado!');
    }

    /**
     * Deletar produto
     */
    public function destroy($id)
    {
        $produto = ProdutoModel::findOrFail($id);
        $dadosProduto = $produto->toArray();

        $produto->delete();

        // Registra exclusão com todos os dados
        AuditoriaService::registrarExclusao('produto', $dadosProduto);

        return redirect()->route('produtos.index')
            ->with('success', 'Produto deletado!');
    }

    /**
     * Atualizar estoque (ação customizada)
     */
    public function atualizarEstoque(Request $request, $id)
    {
        $produto = ProdutoModel::findOrFail($id);
        $estoqueAntigo = $produto->estoque ?? 0;

        $validated = $request->validate([
            'estoque' => 'required|integer|min:0',
            'motivo' => 'nullable|string|max:255',
        ]);

        $produto->update(['estoque' => $validated['estoque']]);

        // Registra alteração de estoque com contexto
        AuditoriaService::registrarAcaoCustomizada(
            acao: 'alterar_estoque',
            recurso: "produto:{$id}",
            descricao: "Estoque alterado de {$estoqueAntigo} para {$validated['estoque']}",
            contexto: [
                'estoque_anterior' => $estoqueAntigo,
                'estoque_novo' => $validated['estoque'],
                'motivo' => $validated['motivo'] ?? 'Não informado',
                'diferenca' => $validated['estoque'] - $estoqueAntigo,
            ]
        );

        return redirect()->back()
            ->with('success', 'Estoque atualizado!');
    }

    /**
     * Ativar/Desativar produto
     */
    public function toggleAtivo($id)
    {
        $produto = ProdutoModel::findOrFail($id);
        $statusAntigo = $produto->ativo;

        $produto->update(['ativo' => !$statusAntigo]);

        // Registra mudança de status
        AuditoriaService::registrarAcaoCustomizada(
            acao: 'alterar_status',
            recurso: "produto:{$id}",
            descricao: $produto->ativo ? 'Produto ativado' : 'Produto desativado',
            contexto: [
                'ativo_anterior' => (bool)$statusAntigo,
                'ativo_novo' => (bool)$produto->ativo,
            ]
        );

        return redirect()->back()
            ->with('success', 'Status do produto alterado!');
    }

    /**
     * Exportar lista de produtos (auditoria de relatório)
     */
    public function exportar(Request $request)
    {
        $filtros = $request->only(['categoria_id', 'preco_min', 'preco_max']);

        // Registra geração de relatório
        AuditoriaService::registrarRelatorioGerado(
            tipoRelatorio: 'produtos_export',
            filtros: $filtros
        );

        // Lógica de exportação...
        return response()->download('produtos.xlsx');
    }
}
