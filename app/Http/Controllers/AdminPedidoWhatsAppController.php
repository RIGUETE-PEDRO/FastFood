<?php

namespace App\Http\Controllers;

use App\Enum\StatusPedidos;
use App\Models\EnderecoModel;
use App\Models\CidadeModel;
use App\Models\FormaPagamentoModel;
use App\Models\ItemPedidoModel;
use App\Models\PedidoModel;
use App\Models\ProdutoModel;
use App\Models\UsuarioModel;
use App\Services\PedidoRealtimeService;
use App\Services\PersonalizacaoProdutoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminPedidoWhatsAppController extends Controller
{
    public function index()
    {
        $produtos = ProdutoModel::with(['categoria', 'bordas'])->where('disponivel', true)->orderBy('nome')->get();
        $pizzas = $produtos->filter(function ($produto) {
            $categoria = mb_strtolower(Str::ascii(trim((string) optional($produto->categoria)->nome)));
            return in_array($categoria, ['pizza', 'pizzas'], true);
        })->values();

        return view('Admin.PedidoWhatsApp', [
            'produtos' => $produtos,
            'pizzas' => $pizzas,
            'categorias' => \App\Models\CategoriaProdutoModel::orderBy('nome')->get(),
            'cidades' => CidadeModel::orderBy('nome')->get(),
            'formasPagamento' => FormaPagamentoModel::orderBy('tipo_pagamento')->get(),
        ]);
    }

    public function buscarCliente(Request $request): JsonResponse
    {
        $telefone = preg_replace('/\D+/', '', (string) $request->query('telefone'));
        if (strlen($telefone) < 8) {
            return response()->json(['encontrado' => false]);
        }

        $usuario = $this->buscarUsuarioPorTelefone($telefone);

        if (!$usuario) {
            return response()->json(['encontrado' => false]);
        }

        $enderecos = EnderecoModel::with('cidade')->where('usuario_id', $usuario->id)->orderByDesc('id')->get();

        return response()->json([
            'encontrado' => true,
            'cliente' => ['id' => $usuario->id, 'nome' => $usuario->nome, 'telefone' => $usuario->telefone],
            'enderecos' => $enderecos->map(fn ($endereco) => [
                'id' => $endereco->id,
                'label' => trim($endereco->logradouro . ', ' . ($endereco->numero ?: 's/n') . ' — ' . $endereco->bairro . ', ' . optional($endereco->cidade)->nome . ($endereco->complemento ? ' (' . $endereco->complemento . ')' : '')),
            ]),
        ]);
    }

    public function store(Request $request, PedidoRealtimeService $realtime, PersonalizacaoProdutoService $personalizacaoProdutoService)
    {
        $dados = $request->validate([
            'usuario_id' => ['nullable', 'integer', 'exists:usuarios,id'],
            'cliente_nome' => ['required_without:usuario_id', 'nullable', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:30'],
            'tipo_entrega' => ['required', 'in:entrega,retirada'],
            'endereco_id' => ['nullable', 'integer', 'exists:endereco,id'],
            'cadastrar_endereco' => ['nullable', 'boolean'],
            'novo_endereco.logradouro' => ['required_if:cadastrar_endereco,1', 'nullable', 'string', 'max:255'],
            'novo_endereco.numero' => ['nullable', 'string', 'max:30'],
            'novo_endereco.complemento' => ['nullable', 'string', 'max:255'],
            'novo_endereco.bairro' => ['required_if:cadastrar_endereco,1', 'nullable', 'string', 'max:255'],
            'novo_endereco.cidade_id' => ['required_if:cadastrar_endereco,1', 'nullable', 'integer', 'exists:cidade,id'],
            'tipo_pagamento_id' => ['required_if:tipo_entrega,entrega', 'nullable', 'integer', 'exists:tipo_pagamento,id'],
            'observacoes_pagamento' => ['nullable', 'string', 'max:500'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*' => ['array'],
            'itens.*.produto_id' => ['required', 'integer', 'exists:produtos,id'],
            'itens.*.quantidade' => ['required', 'integer', 'min:1', 'max:99'],
            'itens.*.observacao' => ['nullable', 'string', 'max:255'],
            'itens.*.personalizacao' => ['nullable', 'array'],
            'itens.*.personalizacao.tipo' => ['nullable', 'in:pizza,porcao'],
            'itens.*.personalizacao.modo' => ['nullable', 'in:inteira,meia_a_meia'],
            'itens.*.personalizacao.tamanho' => ['nullable', 'in:P,M,G,GG,XG'],
            'itens.*.personalizacao.borda' => ['nullable', 'in:sem_borda,com_borda'],
            'itens.*.personalizacao.borda_id' => ['nullable', 'integer', 'min:0'],
            'itens.*.personalizacao.sabor_2_id' => ['nullable', 'integer', 'min:1'],
            'itens.*.personalizacao.tipo_porcao' => ['nullable', 'in:inteira,meia'],
        ]);

        $telefoneNormalizado = preg_replace('/\D+/', '', $dados['telefone']);
        if (strlen($telefoneNormalizado) < 8) {
            throw ValidationException::withMessages(['telefone' => 'Informe um telefone válido para cadastrar o cliente.']);
        }

        $usuario = !empty($dados['usuario_id']) ? UsuarioModel::findOrFail($dados['usuario_id']) : null;
        $enderecoId = $dados['endereco_id'] ?? null;
        if ($dados['tipo_entrega'] === 'entrega') {
            $temNovoEndereco = $request->boolean('cadastrar_endereco');
            if ($temNovoEndereco) {
                $enderecoId = null;
            } elseif (!$usuario || !$enderecoId || !EnderecoModel::where('id', $enderecoId)->where('usuario_id', $usuario->id)->exists()) {
                throw ValidationException::withMessages(['endereco_id' => 'Selecione um endereço salvo deste cliente para entrega.']);
            }
        } else {
            $enderecoId = null;
        }

        $pedido = DB::transaction(function () use ($dados, $usuario, $enderecoId, $request, $telefoneNormalizado, $personalizacaoProdutoService) {
            if (!$usuario) {
                $usuario = $this->buscarUsuarioPorTelefone($telefoneNormalizado);
                if (!$usuario) {
                    $usuario = UsuarioModel::create([
                        'nome' => $dados['cliente_nome'],
                        'email' => 'whatsapp.' . $telefoneNormalizado . '.' . bin2hex(random_bytes(4)) . '@cliente.local',
                        'senha' => bcrypt(bin2hex(random_bytes(24))),
                        'telefone' => $dados['telefone'],
                        'tipo_usuario_id' => \App\Enum\TipoUsuario::CLIENTE->value,
                    ]);
                }
            }

            if ($dados['tipo_entrega'] === 'entrega' && !$request->boolean('cadastrar_endereco')
                && (!$enderecoId || !EnderecoModel::where('id', $enderecoId)->where('usuario_id', $usuario->id)->exists())) {
                throw ValidationException::withMessages(['endereco_id' => 'Selecione um endereço salvo deste cliente ou cadastre um novo.']);
            }

            if ($dados['tipo_entrega'] === 'entrega' && $request->boolean('cadastrar_endereco')) {
                $endereco = EnderecoModel::create(array_merge($dados['novo_endereco'], ['usuario_id' => $usuario->id]));
                $enderecoId = $endereco->id;
            }
            $produtos = ProdutoModel::with(['categoria', 'bordas'])
                ->whereIn('id', collect($dados['itens'])->pluck('produto_id'))
                ->get()
                ->keyBy('id');
            $total = 0;
            $itensCotados = [];
            foreach ($dados['itens'] as $item) {
                $produto = $produtos->get((int) $item['produto_id']);
                if (!$produto || !$produto->disponivel) {
                    throw ValidationException::withMessages(['itens' => 'Um dos produtos não está disponível. Atualize a tela e tente novamente.']);
                }

                try {
                    $cotacao = $personalizacaoProdutoService->cotar($produto, $item['personalizacao'] ?? null);
                } catch (\InvalidArgumentException $e) {
                    throw ValidationException::withMessages(['itens' => $e->getMessage()]);
                }

                $itemCotado = [
                    'produto' => $produto,
                    'quantidade' => (int) $item['quantidade'],
                    'observacao' => $item['observacao'] ?? null,
                    'personalizacao' => $cotacao['personalizacao'],
                    'preco_unitario' => $cotacao['preco_unitario'],
                ];
                $itensCotados[] = $itemCotado;
                $total += $itemCotado['preco_unitario'] * $itemCotado['quantidade'];
            }

            $pedido = PedidoModel::create([
                'usuario_id' => $usuario->id,
                'endereco_id' => $enderecoId,
                'tipo_pagamento_id' => $dados['tipo_pagamento_id'] ?? null,
                'observacoes_pagamento' => $dados['observacoes_pagamento'] ?? null,
                'valor_total' => $total,
                'status' => StatusPedidos::PENDENTE->value,
            ]);

            foreach ($itensCotados as $item) {
                $produto = $item['produto'];
                ItemPedidoModel::create([
                    'produto_id' => $produto->id,
                    'pedido_id' => $pedido->id,
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                    'observacao' => $item['observacao'],
                    'personalizacao' => $item['personalizacao'],
                    'status_da_comanda' => 'em_aberto',
                ]);
            }

            return $pedido;
        });

        $realtime->broadcast($pedido->id);

        return redirect()->route('admin.pedidos.whatsapp')->with('sucesso', 'Pedido #' . $pedido->id . ' cadastrado como pendente.');
    }

    private function buscarUsuarioPorTelefone(string $telefone): ?UsuarioModel
    {
        $alternativas = [$telefone];
        if (str_starts_with($telefone, '55') && in_array(strlen($telefone), [12, 13], true)) {
            $alternativas[] = substr($telefone, 2);
        } elseif (in_array(strlen($telefone), [10, 11], true)) {
            $alternativas[] = '55' . $telefone;
        }

        $normalizadoSql = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefone, ' ', ''), '(', ''), ')', ''), '-', ''), '+', '')";

        return UsuarioModel::query()
            ->where(function ($query) use ($normalizadoSql, $alternativas) {
                foreach ($alternativas as $indice => $alternativa) {
                    $metodo = $indice === 0 ? 'whereRaw' : 'orWhereRaw';
                    $query->{$metodo}("{$normalizadoSql} = ?", [$alternativa]);
                }
            })
            ->first();
    }
}
