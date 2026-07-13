<?php use \FriendsOfBotble\Honeypot\Facades\Honeypot; ?>

<div id="<?php echo e($fieldName = Honeypot::randomFieldName()); ?>_wrap" style="display: none" aria-hidden="true">
    <input id="<?php echo e($fieldName); ?>"
           name="<?php echo e($fieldName); ?>"
           type="text"
           value="<?php echo e(Str::random(10)); ?>"
           autocomplete="nope"
           tabindex="-1">
    <input name="<?php echo e(Honeypot::validFromFieldName()); ?>"
           type="text"
           value="<?php echo e(Honeypot::encryptedValidFrom()); ?>"
           autocomplete="off"
           tabindex="-1">
</div>

<?php if(Honeypot::getSetting('show_disclaimer')): ?>
    <div class="honeypot-disclaimer" style="display: block; background-color: rgb(232 233 235); border-radius: 4px; padding: 16px; margin-bottom: 16px; ">
        <?php echo BaseHelper::clean(trans('plugins/fob-honeypot::honeypot.disclaimer')); ?>

    </div>

    <style>
        body[data-bs-theme="dark"] .captcha-disclaimer {
            background-color: transparent !important;
            border: var(--bb-border-width) solid var(--bb-border-color) !important;
        }
    </style>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/fob-honeypot/resources/views/honeypot.blade.php ENDPATH**/ ?>