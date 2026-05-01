<script type="text/javascript">
    var BotbleVariables = BotbleVariables || {};

    <?php if(Auth::guard()->check()): ?>
        BotbleVariables.languages = {
            tables: <?php echo e(Js::from(trans('core/base::tables'))); ?>,
            notices_msg: <?php echo e(Js::from(trans('core/base::notices'))); ?>,
            pagination: <?php echo e(Js::from(trans('pagination'))); ?>,
        };
        BotbleVariables.authorized =
            "<?php echo e(setting('membership_authorization_at') && Carbon\Carbon::now()->diffInDays(Carbon\Carbon::createFromFormat('Y-m-d H:i:s', setting('membership_authorization_at'))) <= 7 ? 1 : 0); ?>";
        BotbleVariables.authorize_url = "<?php echo e(route('membership.authorize')); ?>";

        BotbleVariables.menu_item_count_url = "<?php echo e(route('menu-items-count')); ?>";
    <?php else: ?>
        BotbleVariables.languages = {
            notices_msg: <?php echo e(Js::from(trans('core/base::notices'))); ?>,
        };
    <?php endif; ?>
</script>

<?php $__env->startPush('footer'); ?>
    <?php if(Session::has('success_msg') || Session::has('error_msg') || (isset($errors) && $errors->any()) || isset($error_msg)): ?>
        <script type="text/javascript">
            $(function() {
                <?php if(Session::has('success_msg')): ?>
                    Botble.showSuccess('<?php echo BaseHelper::cleanToastMessage(session('success_msg')); ?>');
                <?php endif; ?>
                <?php if(Session::has('error_msg')): ?>
                    Botble.showError('<?php echo BaseHelper::cleanToastMessage(session('error_msg')); ?>');
                <?php endif; ?>
                <?php if(isset($error_msg)): ?>
                    Botble.showError('<?php echo BaseHelper::cleanToastMessage($error_msg); ?>');
                <?php endif; ?>
                <?php if(isset($errors)): ?>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        Botble.showError('<?php echo BaseHelper::cleanToastMessage($error); ?>');
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            })
        </script>
    <?php endif; ?>
<?php $__env->stopPush(); ?>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/elements/common.blade.php ENDPATH**/ ?>