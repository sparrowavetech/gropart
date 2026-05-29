<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'name' => null,
    'checked' => false,
    'label' => null,
    'id' => null,
    'single' => false,
    'wrapperClass' => null,
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
    'label',
    'name' => null,
    'checked' => false,
    'label' => null,
    'id' => null,
    'single' => false,
    'wrapperClass' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php ($id = $attributes->get('id', $name) ?? Str::random(8)); ?>

<label class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'form-check form-switch d-inline-block',
    'form-check-single' => $single,
    $wrapperClass,
]); ?>">
    <input
        name="<?php echo e($name); ?>"
        type="hidden"
        value="0"
    />
    <input
        <?php echo e($attributes->merge(['class' => 'form-check-input', 'name' => $name, 'type' => 'checkbox', 'value' => '1', 'id' => $id])); ?>

        <?php if($checked): echo 'checked'; endif; ?>
    />

    <?php if($label): ?>
        <span class="form-check-label"><?php echo $label; ?></span>
    <?php endif; ?>
</label>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/components/form/toggle.blade.php ENDPATH**/ ?>