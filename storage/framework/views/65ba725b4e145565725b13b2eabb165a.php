<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'button',
    'tag' => 'button',
    'disabled' => false,
    'color' => null,
    'icon' => null,
    'iconOnly' => false,
    'square' => false,
    'pill' => false,
    'iconPosition' => 'left',
    'outlined' => false,
    'size' => null,
    'loading' => false,
    'loadingOverlay' => false,
    'tooltip' => null,
    'tooltipPlacement' => 'top',
    'ghost' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'type' => 'button',
    'tag' => 'button',
    'disabled' => false,
    'color' => null,
    'icon' => null,
    'iconOnly' => false,
    'square' => false,
    'pill' => false,
    'iconPosition' => 'left',
    'outlined' => false,
    'size' => null,
    'loading' => false,
    'loadingOverlay' => false,
    'tooltip' => null,
    'tooltipPlacement' => 'top',
    'ghost' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $class = Arr::toCssClasses([
        'btn',
        'disabled' => $disabled,
        'btn-square' => $square,
        'btn-pill' => $pill,
        'btn-icon' => $iconOnly,
        'btn-loading' => $loading && $loadingOverlay,
        ...$outlined ? [$color ? "btn-outline-$color" : null] : [$color ? "btn-$color" : null],
        match ($size) {
            'sm' => 'btn-sm',
            'lg' => 'btn-lg',
            default => null,
        },
        "btn-ghost-$color" => $ghost && $color,
    ]);

    $spinnerClasses = Arr::toCssClasses([
        'spinner-border',
        'spinner-border-sm',
        'me-2' => $iconPosition === 'left',
        'ms-2' => $iconPosition === 'right',
    ]);
?>

<<?php echo e($tag); ?>

    <?php echo e($attributes->merge([
            'type' => $type,
            'disabled' => $disabled,
        ])->class($class)); ?>

    <?php if($tooltip): ?> data-bs-toggle="tooltip"
        data-bs-placement="<?php echo e($tooltipPlacement); ?>"
        title="<?php echo e($tooltip); ?>" <?php endif; ?>
>
    <?php if($icon && $iconPosition === 'left'): ?>
        <?php if($loading): ?>
            <span
                class="<?php echo e($spinnerClasses); ?>"
                role="status"
            ></span>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => $icon,'size' => $size] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'icon-left']); ?>
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
        <?php endif; ?>
    <?php endif; ?>

    <?php if($slot->isNotEmpty()): ?>
        <?php if($iconOnly): ?>
            <span class="sr-only"><?php echo e($slot); ?></span>
        <?php else: ?>
            <?php echo e($slot); ?>

        <?php endif; ?>
    <?php endif; ?>

    <?php if($icon && $iconPosition === 'right'): ?>
        <?php if($loading): ?>
            <span
                class="<?php echo e($spinnerClasses); ?>"
                role="status"
            ></span>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => $icon,'size' => $size] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'icon-right']); ?>
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
        <?php endif; ?>
    <?php endif; ?>
    </<?php echo e($tag); ?>>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/components/button.blade.php ENDPATH**/ ?>