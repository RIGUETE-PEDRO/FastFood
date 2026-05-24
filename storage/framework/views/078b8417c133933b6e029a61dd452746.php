<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci minha Senha</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/Login.css')); ?>">
</head>
<body>
   <div class="login-container">

    <div class="login-image">
        <img src="<?php echo e(asset('img/login-imagem.png')); ?>" alt="Recuperar Senha">
    </div>

    <div class="container">
        <h1>Recuperar Senha</h1>
        <p style="text-align: center; font-size: 14px; color: #666; margin-bottom: 20px;">
            Digite seu e-mail para receber as instruções de recuperação de senha.
        </p>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('senha.recuperar')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="input">
                <label for="email" class="text">E-mail:</label>
                <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>" required placeholder="seu@email.com">
            </div>

            <button type="submit">Enviar Link de Recuperação</button>

            <a href="/login" class="register">Voltar para Login</a>
        </form>
    </div>

</div>

<?php echo $__env->make('components.flash-toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Esqueci-senha.blade.php ENDPATH**/ ?>