<style>
    [v-cloak],
    [x-cloak] {
        display: none;
    }
</style>

<?php echo BaseHelper::googleFonts(
    'https://fonts.googleapis.com/' .
        sprintf(
            'css2?family=%s:wght@300;400;500;600;700&display=swap',
            urlencode(setting('admin_primary_font', 'Inter')),
        ),
); ?>


<style>
    :root {
        --primary-font: "<?php echo e(setting('admin_primary_font', 'Inter')); ?>";
        --primary-color: <?php echo e($primaryColor = setting('admin_primary_color', '#206bc4')); ?>;
        --primary-color-rgb: <?php echo e(implode(', ', BaseHelper::hexToRgb($primaryColor))); ?>;
        --secondary-color: <?php echo e($secondaryColor = setting('admin_secondary_color', '#6c7a91')); ?>;
        --secondary-color-rgb: <?php echo e(implode(', ', BaseHelper::hexToRgb($secondaryColor))); ?>;
        --heading-color: <?php echo e(setting('admin_heading_color', 'inherit')); ?>;
        --text-color: <?php echo e($textColor = setting('admin_text_color', '#182433')); ?>;
        --text-color-rgb: <?php echo e(implode(', ', BaseHelper::hexToRgb($textColor))); ?>;
        --link-color: <?php echo e($linkColor = setting('admin_link_color', '#206bc4')); ?>;
        --link-color-rgb: <?php echo e(implode(', ', BaseHelper::hexToRgb($linkColor))); ?>;
        --link-hover-color: <?php echo e($linkHoverColor = setting('admin_link_hover_color', '#206bc4')); ?>;
        --link-hover-color-rgb: <?php echo e(implode(', ', BaseHelper::hexToRgb($linkHoverColor))); ?>;
    }
</style>

<?php echo Assets::renderHeader(['core']); ?>

<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/components/layouts/header.blade.php ENDPATH**/ ?>