<?php
    SeoHelper::setTitle(__('404 - Not found'));
    Theme::fireEventGlobalAssets();
    AdminBar::setIsDisplay(false);
?>

<?php echo Theme::partial('header'); ?>


<div id="main-content">
    <div class="container-xxxl">
        <div class="row justify-content-center">
            <div class="col-md-6 mt-5">
                <div class="error-404 not-found text-center my-5">
                    <?php if(theme_option('404_page_image')): ?>
                        <img
                            src="<?php echo e(RvMedia::getImageUrl(theme_option('404_page_image'))); ?>"
                            alt="404"
                        >
                    <?php else: ?>
                        <img
                            src="<?php echo e(Theme::asset()->url('images/404.png')); ?>"
                            alt="404"
                        >
                    <?php endif; ?>
                    <h2 class="fw-bold h1 page-title"><?php echo e(__('Oops! Page not found.')); ?></h2>
                    <div class="page-content">
                        <div class="my-3"><?php echo e(__("We can't find the page you're looking for.")); ?>

                            <?php echo e(__('You can either')); ?>

                            <a
                                class="text-primary"
                                href="javascript:history.go(-1)"
                            ><?php echo e(__('return to the previous page')); ?></a>,
                            <a
                                class="text-primary"
                                href="<?php echo e(BaseHelper::getHomepageUrl()); ?>"
                            ><?php echo e(__('visit our home page')); ?></a>
                            <?php if(is_plugin_active('blog') || is_plugin_active('ecommerce')): ?>
                                <?php echo e(__('or search for something else.')); ?>

                            <?php endif; ?>
                        </div>
                        <?php if(is_plugin_active('ecommerce') || is_plugin_active('blog')): ?>
                            <form
                                class="search-form"
                                role="search"
                                method="GET"
                                action="<?php echo e(is_plugin_active('ecommerce') ? route('public.products') : route('public.search')); ?>"
                            >
                                <label>
                                    <span class="screen-reader-text"><?php echo e(__('Search for')); ?>:</span>
                                    <input
                                        class="search-field"
                                        name="q"
                                        type="search"
                                        placeholder="<?php echo e(__('Search...')); ?>"
                                    >
                                </label>
                                <input
                                    class="search-submit"
                                    type="submit"
                                    value="<?php echo e(__('Search')); ?>"
                                >
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo Theme::partial('footer'); ?>

<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/views/404.blade.php ENDPATH**/ ?>