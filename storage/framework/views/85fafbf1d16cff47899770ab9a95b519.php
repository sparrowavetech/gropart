<div id="whatsapp-floating-button"></div>

<style>
    #whatsapp-floating-button {
        left: <?php echo e(setting('whatsapp-floating-button.position', 'right') ? 'auto' : setting('whatsapp-floating-button.offset_x', 20) . 'px'); ?> !important;
        right: <?php echo e(setting('whatsapp-floating-button.position', 'right') ? setting('whatsapp-floating-button.offset_x', 20) . 'px' : 'auto'); ?> !important;
        bottom: <?php echo e(setting('whatsapp-floating-button.offset_y', 20)); ?>px !important;
    }
</style>

<script>
    window.addEventListener('load', function() {
        const whatsappFloatingButton = document.getElementById('whatsapp-floating-button');

        if (whatsappFloatingButton) {
            $(whatsappFloatingButton).floatingWhatsApp({
                phone: "<?php echo e(setting('whatsapp-floating-button.phone_number')); ?>",
                popupMessage: "<?php echo e(Str::limit(setting('whatsapp-floating-button.popup_message'), 220)); ?>",
                showPopup: "<?php echo e(setting('whatsapp-floating-button.show_popup', false)); ?>",
                headerTitle: "<?php echo e(setting('whatsapp-floating-button.popup_title')); ?>",
                position: "<?php echo e(setting('whatsapp-floating-button.position', 'right')); ?>",
                size: "<?php echo e(setting('whatsapp-floating-button.size', 60)); ?>px",
                backgroundColor: '#25D366',
                showOnIE: !0,
                autoOpenTimeout: 0,
                headerColor: '#128C7E',
                zIndex: <?php echo e(setting('whatsapp-floating-button.z_index', 999)); ?>,
            });
        }
    });
</script>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/whatsapp-floating-button/resources/views/show.blade.php ENDPATH**/ ?>