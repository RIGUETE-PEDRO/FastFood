<?php ($faviconVersion = file_exists(public_path('img/logo.png')) ? filemtime(public_path('img/logo.png')) : time()); ?>
<link rel="shortcut icon" type="image/png" href="<?php echo e(asset('img/logo.png')); ?>?v=<?php echo e($faviconVersion); ?>">
<link rel="icon" type="image/png" href="<?php echo e(asset('img/logo.png')); ?>?v=<?php echo e($faviconVersion); ?>">
<link rel="apple-touch-icon" href="<?php echo e(asset('img/logo.png')); ?>?v=<?php echo e($faviconVersion); ?>">
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/partials/favicon.blade.php ENDPATH**/ ?>