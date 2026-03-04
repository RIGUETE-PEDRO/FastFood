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
    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">☰</span>
                Menu
            </button>

    <main
        class="container py-4 pedidos-admin"
        id="pedidos-admin-root"
        data-polling-url="{{ route('Pedidos.Poll') }}"
        data-checksum="{{ $realtimeChecksum ?? '' }}"
    >
        <header class="cabecalho-pedidos mb-4">
            <div class="cabecalho-pedidos__titulo">
                <span class="cabecalho-pedidos__etiqueta">Painel de pedidos</span>
                <h1 class="titulo-pagina">Olá, {{ $nomeUsuario ?? 'Administrador' }}!</h1>
                <p class="texto-suave mb-0">Acompanhe o avanço de cada pedido e atualize o status conforme o preparo.</p>
            </div>
            <div class="cabecalho-pedidos__dados">
                <span class="badge-total" id="pedidos-total-badge">{{ $totalPedidos }} pedidos ativos</span>
                <span class="badge-perfil">{{ $tipoUsuario ?? 'Equipe' }}</span>
            </div>
        </header>

        <div id="pedidos-resumo-wrapper">
            @include('Admin.partials.pedidos-resumo-cards', ['dashboardCards' => $dashboardCards])
        </div>

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

        <div id="pedidos-lista-wrapper">
            @include('Admin.partials.pedidos-lista', [
                'pedidos' => $pedidos,
                'pedidosPorStatus' => $pedidosPorStatus,
                'statusOptions' => $statusOptions,
                'statusTimeline' => $statusTimeline,
                'statusLabels' => $statusLabels,
            ])
        </div>
    </main>
        </div>
    </div>
</body>

</html>
