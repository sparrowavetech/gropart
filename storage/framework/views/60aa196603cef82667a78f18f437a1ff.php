<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'value', 'key', 'checked' => false, 'inline' => true, 'single' => false]));

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

foreach (array_filter((['name', 'value', 'key', 'checked' => false, 'inline' => true, 'single' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $labelClasses = Arr::toCssClasses(['form-check', 'form-check-inline' => $inline, 'form-check-single' => $single]);
?>

<label class="<?php echo e($labelClasses); ?>">
    <input
        <?php echo e($attributes->merge(['class' => 'form-check-input'])); ?>

        type="radio"
        name="<?php echo e($name); ?>"
        value="<?php echo e($value); ?>"
        <?php if(old($name) === $value || $checked): echo 'checked'; endif; ?>
    >

    <span class="form-check-label"><?php echo e($slot); ?></span>
</label>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/components/form/radio.blade.php ENDPATH**/ ?>