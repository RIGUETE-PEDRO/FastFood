<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrativo</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">

</head>

<body>
    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span> Menu
            </button>
    <main>
        <div class="dashboard">
            <div class="painel">Total de vendas</div>
            <div class="painel">Quantidade de Pedidos</div>
            <div class="painel">Produto mais vendido</div>
        </div>


    </main>
        </div>
    </div>

</body>

</html>
