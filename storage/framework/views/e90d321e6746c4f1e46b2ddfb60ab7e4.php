<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Login</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
     <link rel="stylesheet" href="<?php echo e(asset('css/Login.css')); ?>">
</head>
<body>
   <div class="login-container">

    <div class="login-image">
        <img src="<?php echo e(asset('img/login-imagem.png')); ?>" alt="Login Image">
    </div>


    <div class="container">
        <div class="brand-mini" aria-label="Logo FlashFood">
            <img src="<?php echo e(asset('img/login-imagem.png')); ?>" alt="Logo FlashFood">
        </div>
        <h1>Login</h1>
        <form action="<?php echo e(route('login')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="input">
                <label for="email" class="text">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input">
                <label for="senha" class="text">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit" id="entrar">Entrar</button>
            <a href="/registro" id="cadastrar" class="register">Cadastre-se</a>
            <a href="/esqueci-senha" id="esqueci" class="esquecer-senha">Esqueci minha senha</a>

        </form>
    </div>
</div>

<?php echo $__env->make('components.flash-toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Login.blade.php ENDPATH**/ ?>