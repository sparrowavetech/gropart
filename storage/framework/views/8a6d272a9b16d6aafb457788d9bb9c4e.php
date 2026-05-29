<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'nowrap' => false,
    'hover' => true,
    'striped' => true,
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
    'nowrap' => false,
    'hover' => true,
    'striped' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = Arr::toCssClasses([
        'table table-vcenter card-table',
        'table-nowrap' => $nowrap,
        'table-hover' => $hover,
        'table-striped' => $striped,
    ]);
?>

<table <?php echo e($attributes->merge(['class' => $classes])); ?>>
    <?php echo e($slot); ?>

</table>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/components/table/index.blade.php ENDPATH**/ ?>