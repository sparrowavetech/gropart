<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label', 'value' => 0, 'icon' => null, 'url' => null, 'color' => 'primary', 'column' => null]));

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

foreach (array_filter((['label', 'value' => 0, 'icon' => null, 'url' => null, 'color' => 'primary', 'column' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $tag = $url ? 'a' : 'div';

    $classes = Arr::toCssClasses([
        'text-white d-block rounded position-relative overflow-hidden text-decoration-none',
        "bg-$color" => !str_contains($color, '#'),
    ]);

    Assets::addScripts(['counterup']);
?>

<div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['col dashboard-widget-item', $column]); ?>">
    <<?php echo e($tag); ?>

        <?php echo e($attributes->merge([
            'class' => $classes,
            'href' => $url,
        ])); ?>

        style="<?php echo \Illuminate\Support\Arr::toCssStyles([
            'background-color: ' . $color => str_contains($color, '#'),
        ]) ?>"
    >
        <div class="d-flex justify-content-between align-items-center">
            <div class="details px-4 py-3 d-flex flex-column justify-content-between">
                <div class="desc fw-medium"><?php echo e($label); ?></div>
                <div class="number fw-bolder">
                    <?php if(is_int($value)): ?>
                        <span
                            data-counter="counterup"
                            data-value="<?php echo e($value); ?>"
                        >0</span>
                    <?php else: ?>
                        <span><?php echo e($value); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="visual ps-1 position-absolute end-0">
                <?php if($icon): ?>
                    <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => $icon] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'me-n2','style' => 'opacity: .1; --bb-icon-size: 80px;']); ?> <?php echo $__env->renderComponent(); ?>
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
            </div>
        </div>
        </<?php echo e($tag); ?>>
</div>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/components/stat-widget/item.blade.php ENDPATH**/ ?>