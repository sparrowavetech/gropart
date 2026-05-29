<?php $__env->startSection('content'); ?>
    <?php if (isset($component)) { $__componentOriginalecda78b9fe8916cbd83b85e55a8b7a1c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalecda78b9fe8916cbd83b85e55a8b7a1c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::alert','data' => ['type' => 'warning','title' => 'Important notes:','important' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'warning','title' => 'Important notes:','important' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
        Those plugins are from our Botble community <a
            href="https://marketplace.botble.com/products"
            target="_blank"
        >marketplace.botble.com/products</a>. We regret to inform
        you that we cannot assume responsibility for the functionality or support of free plugins, as they are
        developed and maintained independently. However, we are more than happy to assist with any inquiries or
        issues related to our official products and services.
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalecda78b9fe8916cbd83b85e55a8b7a1c)): ?>
<?php $attributes = $__attributesOriginalecda78b9fe8916cbd83b85e55a8b7a1c; ?>
<?php unset($__attributesOriginalecda78b9fe8916cbd83b85e55a8b7a1c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalecda78b9fe8916cbd83b85e55a8b7a1c)): ?>
<?php $component = $__componentOriginalecda78b9fe8916cbd83b85e55a8b7a1c; ?>
<?php unset($__componentOriginalecda78b9fe8916cbd83b85e55a8b7a1c); ?>
<?php endif; ?>

    <v-plugin-list
        plugin-list-url="<?php echo e(route('plugins.marketplace.ajax.list')); ?>"
        plugin-remove-url="<?php echo e(route('plugins.remove', '__name__')); ?>"
    ></v-plugin-list>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('footer'); ?>
    <?php if (isset($component)) { $__componentOriginaldc8ac54b6bf7eb0d0560fdd5aa630687 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldc8ac54b6bf7eb0d0560fdd5aa630687 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::modal','data' => ['id' => 'terms-and-policy-modal','title' => trans('packages/plugin-management::plugin.install_plugin_from_marketplace'),'submitButtonLabel' => trans('packages/plugin-management::plugin.accept_and_install'),'size' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'terms-and-policy-modal','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(trans('packages/plugin-management::plugin.install_plugin_from_marketplace')),'submit-button-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(trans('packages/plugin-management::plugin.accept_and_install')),'size' => 'md']); ?>
        <div class="text-start">
            <p>
                You are installing plugin from our Botble community. Those plugins are developed by author
                on <a
                    href="https://marketplace.botble.com"
                    target="_blank"
                >marketplace.botble.com</a>.
            </p>
            <p>We (Botble) <strong>won't</strong> support free plugins from Marketplace.</p>
            <p>
                If it has issues or bugs, please contact the author of that plugin to get support or just
                delete it from <a :href="installedPluginsUrl">Installed Plugins</a> page.
            </p>
            <p class="mb-0">
                If it makes your site down, just delete that plugin from
                <code>platform/plugins</code> folder.
            </p>
        </div>

         <?php $__env->slot('footer', null, []); ?> 
            <div class="w-100">
                <div class="row">
                    <div class="col">
                        <button
                            type="button"
                            class="btn w-100"
                            data-bs-dismiss="modal"
                        >
                            <?php echo e(trans('packages/plugin-management::plugin.cancel')); ?>

                        </button>
                    </div>
                    <div class="col">
                        <button
                            type="button"
                            class="btn btn-info w-100"
                            data-bb-toggle="accept-term-and-policy"
                        >
                            <?php echo e(trans('packages/plugin-management::plugin.accept_and_install')); ?>

                        </button>
                    </div>
                </div>
            </div>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldc8ac54b6bf7eb0d0560fdd5aa630687)): ?>
<?php $attributes = $__attributesOriginaldc8ac54b6bf7eb0d0560fdd5aa630687; ?>
<?php unset($__attributesOriginaldc8ac54b6bf7eb0d0560fdd5aa630687); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldc8ac54b6bf7eb0d0560fdd5aa630687)): ?>
<?php $component = $__componentOriginaldc8ac54b6bf7eb0d0560fdd5aa630687; ?>
<?php unset($__componentOriginaldc8ac54b6bf7eb0d0560fdd5aa630687); ?>
<?php endif; ?>

    <script>
        window.trans = <?php echo e(Js::from([
            'base' => trans('packages/plugin-management::marketplace'),
        ])); ?>;

        window.marketplace = {
            route: {
                list: "<?php echo e(route('plugins.marketplace.ajax.list')); ?>",
                detail: "<?php echo e(route('plugins.marketplace.ajax.detail', [':id'])); ?>",
                install: "<?php echo e(route('plugins.marketplace.ajax.install', [':id'])); ?>",
                active: "<?php echo e(route('plugins.change.status')); ?>",
            },
            installed: <?php echo e(Js::from(get_installed_plugins())); ?>,
            activated: <?php echo e(Js::from(get_active_plugins())); ?>,
            token: "<?php echo e(csrf_token()); ?>",
            coreVersion: "<?php echo e(get_cms_version()); ?>"
        };
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make(BaseHelper::getAdminMasterLayoutTemplate(), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp82\htdocs\gropart\platform\packages\plugin-management\/resources/views/marketplace.blade.php ENDPATH**/ ?>