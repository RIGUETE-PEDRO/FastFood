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
<nav class="navbar navbar-expand-lg bg-body-tertiary navbar" >
  <div class="container-fluid navbar" >
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
          <a class="nav-link text navegador" href="#">Gerenciamento de Funcionarios</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text navegador" href="#">Pedidos</a>
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
            <li><a class="dropdown-item text" href="#">Gerenciamento de produtos</a></li>
              <li><hr class="dropdown-divider"></li>
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
                    <img class="profile-image" id="preview-image" src="{{ $usuario->url_imagem_perfil ? asset('img/perfil/' . $usuario->url_imagem_perfil) : asset('img/person.avif') }}" alt="Foto do usuário">
                                <label for="foto-upload" class="profile-image-overlay">
                </div>
                <span class="ms-2 text" >{{ $nomeUsuario }}</span>


            </a>
                <ul class="dropdown-menu dropdown-menu-end list">
                <li><a class="dropdown-item text" href="{{ route('perfil') }}">Perfil</a></li>
                <li><a class="dropdown-item text" href="#">Configurações</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text" href="{{ route('logout') }}">Sair</a></li>
            </ul>


        </li>


      </ul>
    </div>
  </div>
</nav>

<main>
 <div class="dashboard">
    <div class="painel">Total de vendas</div>
    <div class="painel">Pedidos hoje</div>
    <div class="painel">Produto mais vendido</div>
</div>


</main>

</body>
</html>
