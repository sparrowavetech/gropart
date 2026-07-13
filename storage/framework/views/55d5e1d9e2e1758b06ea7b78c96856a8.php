<?php
    $title = theme_option('newsletter_popup_title');
    $desktopImage = theme_option('newsletter_popup_image');
    $tabletImage = theme_option('newsletter_popup_tablet_image') ?: $desktopImage;
    $mobileImage = theme_option('newsletter_popup_mobile_image') ?: $tabletImage;
    $hasImage = $desktopImage || $tabletImage || $mobileImage;
?>

<link
    rel="stylesheet"
    href="<?php echo e(asset('vendor/core/plugins/newsletter/css/newsletter.css')); ?>?v=1.4.0"
>

<div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['modal-dialog', 'modal-lg' => $hasImage]); ?>">
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'modal-content border-0',
        'd-flex flex-md-col flex-lg-row' => $hasImage,
    ]); ?>">
        <?php if($hasImage): ?>
            <div class="col-md-6 newsletter-popup-bg">
                <picture>
                    <?php if($desktopImage): ?>
                        <source
                            srcset="<?php echo e(RvMedia::getImageUrl($desktopImage, null, false, RvMedia::getDefaultImage())); ?>"
                            media="(min-width: 1200px)"
                        >
                    <?php endif; ?>
                    <?php if($tabletImage): ?>
                        <source
                            srcset="<?php echo e(RvMedia::getImageUrl($tabletImage, null, false, RvMedia::getDefaultImage())); ?>"
                            media="(min-width: 768px)"
                        >
                    <?php endif; ?>
                    <?php if($mobileImage): ?>
                        <source
                            srcset="<?php echo e(RvMedia::getImageUrl($mobileImage, null, false, RvMedia::getDefaultImage())); ?>"
                            media="(max-width: 767px)"
                        >
                    <?php endif; ?>
                    <?php echo RvMedia::image($mobileImage ?: $tabletImage ?: $desktopImage, $title, attributes: ['loading' => 'eager', 'class' => 'newsletter-popup-image']); ?>

                </picture>
            </div>
        <?php endif; ?>

        <button
            type="button"
            class="btn-close position-absolute"
            data-bs-dismiss="modal"
            data-dismiss="modal"
            aria-label="Close"
        ></button>

        <div class="newsletter-popup-content">
            <div class="modal-header flex-column align-items-start border-0 p-0">
                <?php if($subtitle = theme_option('newsletter_popup_subtitle')): ?>
                    <span class="modal-subtitle"><?php echo BaseHelper::clean($subtitle); ?></span>
                <?php endif; ?>

                <?php if($title): ?>
                    <h5
                        class="modal-title fs-2"
                        id="newsletterPopupModalLabel"
                    ><?php echo BaseHelper::clean($title); ?></h5>
                <?php endif; ?>

                <?php if($description = theme_option('newsletter_popup_description')): ?>
                    <p class="modal-text text-muted"><?php echo BaseHelper::clean($description); ?></p>
                <?php endif; ?>
            </div>
            <div class="modal-body p-0">
                <?php echo $newsletterForm->setFormOption('class', 'bb-newsletter-popup-form')->renderForm(); ?>

            </div>
        </div>
    </div>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/newsletter/resources/views/partials/popup.blade.php ENDPATH**/ ?>