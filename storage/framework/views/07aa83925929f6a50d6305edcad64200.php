<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label', 'description' => null, 'helperText' => null]));

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

foreach (array_filter((['label', 'description' => null, 'helperText' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<label <?php echo e($attributes->merge(['class' => 'form-label'])); ?>>
    <?php echo e($label ?? $slot); ?>


    <?php if($description): ?>
        <span class="form-label-description">
            <?php echo $description; ?>

        </span>
    <?php endif; ?>

    <?php if($helperText): ?>
        <span
            class="form-help"
            data-bs-toggle="popover"
            data-bs-placement="top"
            data-bs-html="true"
            data-bs-content="<?php echo e($helperText); ?>"
        >?</span>
    <?php endif; ?>
</label>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/components/form/label.blade.php ENDPATH**/ ?>