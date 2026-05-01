<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => null,
    'label' => null,
    'name' => null,
    'value' => null,
    'checked' => false,
    'helperText' => null,
    'inline' => false,
    'single' => false,
    'marginZero' => false,
    'noMargin' => false,
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
    'id' => null,
    'label' => null,
    'name' => null,
    'value' => null,
    'checked' => false,
    'helperText' => null,
    'inline' => false,
    'single' => false,
    'marginZero' => false,
    'noMargin' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $labelClasses = Arr::toCssClasses([
        'form-check',
        'form-check-inline mb-3' => $inline && !$noMargin,
        'form-check-inline' => $inline && $noMargin,
        'form-check-single' => $single,
        'form-check m-0' => $marginZero,
    ]);

    if (isset($attributes['label_attr'])) {
        $labelAttr = $attributes['label_attr'];
        $labelAttr['class'] .= ' ' . $labelClasses;
        $labelAttr['class'] = trim(str_replace('form-label', '', $labelAttr['class']));
        unset($attributes['label_attr']);
    } else {
        $labelAttr = ['class' => $labelClasses];
    }
?>

<label <?php echo Html::attributes($labelAttr); ?>>
    <input
        <?php echo e($attributes->merge(['type' => 'checkbox', 'id' => $id, 'name' => $name, 'class' => 'form-check-input'])); ?>

        value="<?php echo e($value); ?>"
        <?php if($name ? old($name, $checked) : $checked): echo 'checked'; endif; ?>
    >

    <?php if($label || $slot->isNotEmpty()): ?>
        <span class="form-check-label">
            <?php echo $label ? BaseHelper::clean($label) : $slot; ?>

        </span>
    <?php endif; ?>

    <?php if($helperText): ?>
        <span class="form-check-description"><?php echo BaseHelper::clean($helperText); ?></span>
    <?php endif; ?>
</label>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/components/form/checkbox.blade.php ENDPATH**/ ?>