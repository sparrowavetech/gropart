<div class="py-3">
    <div class="container-xxxl">
        <?php if($shortcode->title): ?>
            <div class="align-items-center mb-2 widget-header">
                <h2 class="col-auto mb-0 py-2"><?php echo e($shortcode->title); ?></h2>
            </div>
        <?php endif; ?>
        <div
            class="row row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-2 justify-content-center my-4 g-2">
            <?php $__currentLoopData = range(1, $shortcode->quantity); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col py-2">
                    <div class="site-info__item d-flex align-items-start">
                        <?php if($icon = $shortcode->{"icon_$i"}): ?>
                            <div class="site-info__image me-3 mt-1">
                                <img
                                    class="lazyload"
                                    data-src="<?php echo e(RvMedia::getImageUrl($icon)); ?>"
                                    alt="<?php echo e($shortcode->{"title_$i"}); ?>"
                                >
                            </div>
                        <?php endif; ?>
                        <div class="site-info__content">
                            <div class="site-info__title h4 fw-bold"><?php echo e($shortcode->{"title_$i"}); ?></div>
                            <div class="site-info__desc"><?php echo BaseHelper::clean(nl2br($shortcode->{"subtitle_$i"})); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/shortcodes/site-features.blade.php ENDPATH**/ ?>