<?php $__currentLoopData = $bodyScripts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $script): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php echo Assets::getHtmlBuilder()->script($script['src'] . Assets::getBuildVersionFor($script['src']), $script['attributes']); ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/assets/footer.blade.php ENDPATH**/ ?>