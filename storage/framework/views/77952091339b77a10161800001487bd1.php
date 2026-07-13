<?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($item->ads_type === 'google_adsense' && $item->google_adsense_slot_id): ?>
        <div <?php echo Html::attributes($attributes); ?>>
            <?php echo $__env->make('plugins/ads::partials.google-adsense.unit-ads-slot', ['slotId' => $item->google_adsense_slot_id], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <?php continue; ?>
    <?php endif; ?>

    <?php if(! $item->image) continue; ?>

    <div <?php echo Html::attributes($attributes); ?>>
        <?php if($item->url): ?>
            <a href="<?php echo e($item->click_url); ?>" <?php if($item->open_in_new_tab): ?> target="_blank" <?php endif; ?> title="<?php echo e(trans('plugins/ads::ads.banner')); ?>">
        <?php endif; ?>
                <picture>
                    <source
                        srcset="<?php echo e($item->image_url); ?>"
                        media="(min-width: 1200px)"
                    />
                    <source
                        srcset="<?php echo e($item->tablet_image_url); ?>"
                        media="(min-width: 768px)"
                    />
                    <source
                        srcset="<?php echo e($item->mobile_image_url); ?>"
                        media="(max-width: 767px)"
                    />

                    <?php echo e(RvMedia::image($item->image_url, $item->name, attributes: ['style' => 'max-width: 100%'])); ?>

                </picture>
        <?php if($item->url): ?>
            </a>
        <?php endif; ?>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/ads/resources/views/partials/ad-display.blade.php ENDPATH**/ ?>