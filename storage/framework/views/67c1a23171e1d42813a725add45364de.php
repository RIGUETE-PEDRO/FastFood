<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Cadastro</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
     <link rel="stylesheet" href="<?php echo e(asset('css/Cadastro.css')); ?>">
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
        <h1>Cadastro</h1>



        <form action="<?php echo e(route('registro')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="input">
                <label for="name" class="text">Nome Completo:</label>
                <input type="text" id="name" name="nome" value="<?php echo e(old('nome')); ?>" required>
            </div>

             <div class="input">
                <label for="phone" class="text">Telefone:</label>
                <input type="text" id="phone" name="telefone" value="<?php echo e(old('telefone')); ?>" required>
            </div>

            <div class="input">
                <label for="email" class="text">E-mail:</label>
                <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>" required>
            </div>

            <div class="input">
                <label for="password" class="text">Senha:</label>
                <input type="password" name="senha" required>

            </div>

            <div class="input">
                <label for="password_confirmation" class="text">Confirme a Senha:</label>
                <input type="password" id="password_confirmation" name="senha_confirmation" required>
            </div>


            <button type="submit">Cadastrar</button>
            <a href="/login" class="register">Já possui uma conta? Faça login</a>

    <?php if(session('sucesso')): ?>
        <label class="msg-sucesso"><?php echo e(session('sucesso')); ?></label>
    <?php endif; ?>


    <?php if(session('erro')): ?>
    <div class="msg-erro">
        <img src="<?php echo e(asset('img/alert.png')); ?>" alt="Erro">
        <span><?php echo e(session('erro')); ?></span>
    </div>
<?php endif; ?>


        </form>
    </div>

    </div>

    <?php echo $__env->make('components.flash-toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Cadastrar.blade.php ENDPATH**/ ?>