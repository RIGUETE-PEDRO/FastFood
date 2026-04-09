<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Painel do Garçom</title>
	@vite(['resources/js/app.js'])
	<link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
</head>

<body>
	<div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">☰</span>
                Menu
            </button>


		<div class="ff-main">
			<button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
				<span class="ff-sidebar-toggle__icon">&#9776;</span>
				Menu
			</button>

			<main>
				<section class="dashboard-header">
					<h1>Painel do Garçom</h1>
					<p>Área de atendimento em construção.</p>
				</section>
			</main>
		</div>
	</div>
</body>

</html>
