<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Painel de Pedidos</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Pedidos.css') }}">
</head>

<body>
     <nav class="navbar navbar-expand-lg bg-body-tertiary navbar">
        <div class="container-fluid navbar">
            <a class="navbar-brand text titulo" href="#">FlashFood</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll navbar" style="--bs-scroll-height: 100px;">
                    <li class="nav-item">
                        <a class="nav-link active text" aria-current="page" href="#">DashBoard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="{{ route('gerenciamento_funcionarios') }}">Gerenciamento de Funcionarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="{{ route('Pedidos.Administrativo') }}">Pedidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Cardápio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Entregas</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text navegador" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Produtos
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end list">
                            <li><a class="dropdown-item text" href="{{ route('gerenciamento_produtos') }}">Gerenciamento de produtos</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text" href="#">Estoque</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true"></a>
                    </li>
                    <!-- Menu do usuário -->
                    <li class="nav-item dropdown ms-auto">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="circulo_maior">
                                <img class="profile-image" id="preview-image" src="{{ $usuario->url_imagem_perfil ? asset('img/perfil/' . $usuario->url_imagem_perfil) : asset('img/person.png') }}" alt="Foto do usuário">
                                <label for="foto-upload" class="profile-image-overlay">
                            </div>
                            <span class="ms-2 text">{{ $nomeUsuario }}</span>


                        </a>
                        <ul class="dropdown-menu dropdown-menu-end list">
                            <li><a class="dropdown-item text" href="{{ route('perfil') }}">Perfil</a></li>
                            <li><a class="dropdown-item text" href="#">Configurações</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text" href="{{ route('logout') }}">Sair</a></li>
                        </ul>


                    </li>


                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4 pedidos-admin">
        <header class="cabecalho-pedidos mb-4">
            <div class="cabecalho-pedidos__titulo">
                <span class="cabecalho-pedidos__etiqueta">Painel de pedidos</span>
                <h1 class="titulo-pagina">Olá, {{ $nomeUsuario ?? 'Administrador' }}!</h1>
                <p class="texto-suave mb-0">Acompanhe o avanço de cada pedido e atualize o status conforme o preparo.</p>
            </div>
            <div class="cabecalho-pedidos__dados">
                <span class="badge-total">{{ $totalPedidos }} pedidos ativos</span>
                <span class="badge-perfil">{{ $tipoUsuario ?? 'Equipe' }}</span>
            </div>
        </header>

        <section class="resumo-pedidos mb-4">
            @foreach($dashboardCards as $card)
                <article class="card-resumo {{ $card['accent'] }}">
                    <span class="card-resumo__rotulo">{{ $card['label'] }}</span>
                    <strong class="card-resumo__valor">{{ $card['valor'] }}</strong>
                </article>
            @endforeach
        </section>

        @if(session('sucesso'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('sucesso') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('erro'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('erro') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($pedidos->isEmpty())
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <h2 class="titulo-card">Nenhum pedido no momento</h2>
                    <p class="texto-suave mb-0">Assim que os clientes realizarem pedidos eles aparecerão aqui.</p>
                </div>
            </div>
        @else
            <section class="lista-pedidos-admin">
                <h2 class="secao-titulo">Pedidos em andamento</h2>
                @forelse(($pedidosPorStatus['abertos'] ?? collect()) as $pedido)
                    @include('Admin.partials.pedido-card', [
                        'pedido' => $pedido,
                        'statusOptions' => $statusOptions,
                        'statusTimeline' => $statusTimeline,
                        'statusLabels' => $statusLabels,
                        'desabilitarAcoes' => false,
                    ])
                @empty
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center py-4">
                            <p class="texto-suave mb-0">Nenhum pedido em preparo ou a caminho agora.</p>
                        </div>
                    </div>
                @endforelse
            </section>

            <section class="acordeao-pedidos">
                <button class="acordeao-pedidos__gatilho" type="button" data-target="#pedidosFinalizados" aria-expanded="false">
                    <span>Pedidos finalizados</span>
                    <span class="acordeao-pedidos__contador">{{ count($pedidosPorStatus['finalizados'] ?? []) }}</span>
                    <span class="acordeao-pedidos__icone" aria-hidden="true"></span>
                </button>
                <div class="acordeao-pedidos__conteudo" id="pedidosFinalizados">
                    @forelse(($pedidosPorStatus['finalizados'] ?? collect()) as $pedido)
                        @include('Admin.partials.pedido-card', [
                            'pedido' => $pedido,
                            'statusOptions' => $statusOptions,
                            'statusTimeline' => $statusTimeline,
                            'statusLabels' => $statusLabels,
                            'desabilitarAcoes' => true,
                        ])
                    @empty
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-4">
                                <p class="texto-suave mb-0">Nenhum pedido entregue ou cancelado ainda.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        @endif
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const gatilhos = document.querySelectorAll('.acordeao-pedidos__gatilho');

            gatilhos.forEach((botao) => {
                const seletor = botao.dataset.target;
                const conteudo = document.querySelector(seletor);

                if (!conteudo) {
                    return;
                }

                botao.addEventListener('click', () => {
                    const expandido = botao.getAttribute('aria-expanded') === 'true';
                    botao.setAttribute('aria-expanded', String(!expandido));

                    if (expandido) {
                        conteudo.classList.remove('is-open');
                        conteudo.style.maxHeight = '0px';
                    } else {
                        conteudo.classList.add('is-open');
                        conteudo.style.maxHeight = `${conteudo.scrollHeight}px`;
                    }
                });

                // Ajusta altura caso já esteja marcado como aberto no HTML
                if (botao.getAttribute('aria-expanded') === 'true') {
                    conteudo.classList.add('is-open');
                    conteudo.style.maxHeight = `${conteudo.scrollHeight}px`;
                }
            });
        });
    </script>
</body>

</html>