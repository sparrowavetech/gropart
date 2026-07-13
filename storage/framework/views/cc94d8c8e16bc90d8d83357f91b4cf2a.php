<?php if($countAds = count($ads)): ?>
    <div class="widget-featured-banners py-5">
        <div class="container-xxxl">
            <div class="row row-cols-lg-<?php echo e($countAds); ?> row-cols-md-<?php echo e($countAds - 1 > 1 ?: 1); ?> row-cols-1 justify-content-center">
                <?php for($i = 0; $i < $countAds; $i++): ?>
                    <div class="col">
                        <div class="featured-banner-item img-fluid-eq my-2">
                            <div class="img-fluid-eq__dummy"></div>
                            <div class="img-fluid-eq__wrap">
                                <?php echo $ads[$i]; ?>

                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/shortcodes/ads/theme-ads.blade.php ENDPATH**/ ?>