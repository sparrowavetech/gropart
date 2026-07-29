<dd><?php echo e($address->name); ?></dd>
<?php if($address->phone): ?>
    <dd>
        <a href="tel:<?php echo e($address->phone); ?>">
            <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-phone'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
            <span dir="ltr"><?php echo e($address->phone); ?></span>
        </a>
    </dd>
<?php endif; ?>

<?php if($address->email): ?>
    <dd><a href="mailto:<?php echo e($address->email); ?>"><?php echo e($address->email); ?></a></dd>
<?php endif; ?>
<?php if($address->address): ?>
    <dd><?php echo BaseHelper::clean($address->address); ?></dd>
<?php endif; ?>
<?php if($address->city): ?>
    <dd><?php echo e($address->city_name); ?></dd>
<?php endif; ?>
<?php if($address->state): ?>
    <dd><?php echo e($address->state_name); ?></dd>
<?php endif; ?>
<?php if($address->country_name): ?>
    <dd><?php echo e($address->country_name); ?></dd>
<?php endif; ?>
<?php if(EcommerceHelper::isZipCodeEnabled() && $address->zip_code): ?>
    <dd><?php echo e($address->zip_code); ?></dd>
<?php endif; ?>
<?php if($address->country || $address->state || $address->city || $address->address): ?>
    <dd>
        <a href="https://maps.google.com/?q=<?php echo e($address->full_address); ?>" target="_blank">
            <?php echo e(trans('plugins/ecommerce::order.see_on_maps')); ?>

        </a>
    </dd>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/ecommerce/resources/views/orders/shipping-address/detail.blade.php ENDPATH**/ ?>