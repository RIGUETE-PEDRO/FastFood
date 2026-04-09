<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Mesas</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Mesa.css') }}">

</head>

<body>
    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span> Menu
            </button>
            <main>


                <section class="mesas-page">
                    <header class="mesas-header">
                        <div>
                            <h1 class="mesas-title">Mesas (Lanchonete)</h1>
                            <p class="mesas-subtitle">Visualização em planta para acompanhar ocupação.</p>
                        </div>
                        <div class="header-actions">
                            <button type="button" class="btn topo btn-Adicionar" data-bs-toggle="modal" data-bs-target="#modalAdicionarMesa">
                                Adicionar Mesa
                            </button>
                            <button type="button" class="btn topo btn-Remover" data-bs-toggle="modal" data-bs-target="#modalRemoverMesa">
                                Remover Mesa
                            </button>
                            <button type="button" class="btn topo btn-Editar" data-bs-toggle="modal" data-bs-target="#modalEditarMesa">
                                Editar Mesa
                            </button>
                        </div>
                    </header>

                    <div class="modal fade" id="modalAdicionarMesa" tabindex="-1" aria-labelledby="modalAdicionarMesaLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header adicionar-mesa-modal">
                                    <h5 class="modal-title" id="modalAdicionarMesaLabel">Cadastrar Nova Mesa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <form action="{{ route('mesas.store') }}" method="POST">
                                    @csrf
                                    <div class="modal-body">

                                        <div class="mb-3">
                                            <label for="numero_da_mesa" class="form-label">Número da Mesa</label>
                                            <input type="number" name="numero_da_mesa" id="numero_da_mesa" class="form-control" placeholder="Ex: 10" required>
                                        </div>


                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status Inicial</label>
                                            <select name="status" id="status" class="form-select">
                                                <option value="Disponivel">Livre</option>
                                                <option value="Ocupada">Ocupada</option>
                                                <option value="Reservada">Reservada</option>
                                            </select>
                                        </div>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Salvar Mesa</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalEditarMesa" tabindex="-1" aria-labelledby="modalEditarMesaLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header editar-mesa-modal">
                                    <h5 class="modal-title" id="modalEditarMesaLabel">Editar Mesa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <form action="{{ route('mesas.update') }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <label class="form-label">Selecione a Mesa para editar</label>
                                        <select name="mesa_id" class="form-select" required>
                                            <option value="">Selecione a Mesa para editar</option>
                                            @foreach ($mesas as $mesa)
                                            <option value="{{ $mesa->id }}">Mesa {{ $mesa->numero_da_mesa }}</option>
                                            @endforeach
                                        </select>

                                        <div class="mb-3">
                                            <label for="numero_da_mesa" class="form-label">Alterar número da Mesa Para</label>
                                            <input type="number" name="numero_da_mesa" id="numero_da_mesa" class="form-control" placeholder="Ex: 10">
                                        </div>


                                        <div class="mb-3">
                                            <label for="status" class="form-label">Novo Status</label>
                                            <select name="status" id="status" class="form-select">
                                                <option value="Disponivel">Livre</option>
                                                <option value="Ocupada">Ocupada</option>
                                                <option value="Reservada">Reservada</option>
                                            </select>
                                        </div>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Salvar Mesa</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalRemoverMesa" tabindex="-1" aria-labelledby="modalRemoverMesaLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header remover-mesa-modal">
                                    <h5 class="modal-title" id="modalRemoverMesaLabel">Remover Mesa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <form action="{{ route('mesas.destroy') }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Selecione a mesa para remover</label>
                                            <select name="mesa_id" class="form-select" required>
                                                <option value=""> Selecione </option>
                                                @foreach ($mesas as $mesa)
                                                <option value="{{ $mesa->id }}">Mesa {{ $mesa->numero_da_mesa }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-danger">Excluir Mesa</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="sala" role="list" aria-label="Mesas">
                        @forelse ($mesas as $mesa)
                        <article class="mesa-card" role="listitem">
                            <div class="mesa-card__top">
                                <span class="mesa-badge status-{{ strtolower($mesa->status) }}">
                                    Status: {{ $mesa->status }}
                                </span>
                            </div>

                            <div class="card-mesa" aria-label="Mesa {{ $mesa->numero_da_mesa }}">
                                <div class="mesa-top">
                                    <span class="label">Mesa {{ $mesa->numero_da_mesa }}</span>

                                    <span class="preco">
                                        R$ {{ number_format((float) $mesa->preco, 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <div class="mesa-card__footer">
                                <a href="{{ route('mesas.detalhes', $mesa->id) }}" class="mesa-btn">
                                    Editar pedidos
                                </a>
                                <a href="{{ route('mesas.detalhes', $mesa->id) }}" class="mesa-btn mesa-btn--ghost">
                                    Dar baixa
                                </a>
                            </div>
                        </article>
                        @empty
                        <p>Nenhuma mesa cadastrada.</p>
                        @endforelse
                    </div>
                </section>


            </main>
        </div>
    </div>

</body>

</html>
