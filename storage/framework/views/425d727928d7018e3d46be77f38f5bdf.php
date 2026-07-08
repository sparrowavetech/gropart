<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => null,
    'title' => null,
    'blur' => true,
    'closeButton' => true,
    'size' => null,
    'contentClass' => null,
    'contentAttrs' => [],
    'bodyClass' => null,
    'bodyAttrs' => [],
    'formAction' => null,
    'formMethod' => 'POST',
    'formAttrs' => [],
    'hasForm' => false,
    'type' => null,
    'buttonId' => null,
    'buttonClass' => null,
    'buttonLabel' => null,
    'centered' => true,
    'scrollable' => false,
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
    'title' => null,
    'blur' => true,
    'closeButton' => true,
    'size' => null,
    'contentClass' => null,
    'contentAttrs' => [],
    'bodyClass' => null,
    'bodyAttrs' => [],
    'formAction' => null,
    'formMethod' => 'POST',
    'formAttrs' => [],
    'hasForm' => false,
    'type' => null,
    'buttonId' => null,
    'buttonClass' => null,
    'buttonLabel' => null,
    'centered' => true,
    'scrollable' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = Arr::toCssClasses(['modal', 'fade', 'modal-blur' => $blur]);

    $hasForm = $hasForm || $formAction;

    $modalContentAttributes = [
        ...$contentAttrs,
        'class' => rtrim('modal-content' . ($contentClass ? ' ' . $contentClass : '')),
    ];
?>

<div
    <?php echo e($attributes->merge(['id' => $id, 'class' => $classes])->class($classes)); ?>

    tabindex="-1"
    role="dialog"
    aria-hidden="true"
    data-select2-dropdown-parent="true"
>
    <div
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'modal-dialog',
            'modal-dialog-centered' => $centered,
            'modal-dialog-scrollable' => $scrollable,
            'modal-dialog-has-form' => $hasForm,
            $size ? "modal-$size" : null,
        ]); ?>"
        role="document"
    >
        <div <?php echo Html::attributes($modalContentAttributes); ?>>
            <?php if($hasForm): ?>
                <form
                    action="<?php echo e($formAction); ?>"
                    method="<?php echo e($formMethod); ?>"
                    <?php echo Html::attributes($formAttrs); ?>

                >
                    <?php echo csrf_field(); ?>
            <?php endif; ?>

            <?php if($title || $closeButton): ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo e($title); ?></h5>
                    <?php if($closeButton): ?>
                        <?php if (isset($component)) { $__componentOriginal78bbf683c893f1d7a5db54eb73c5b865 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78bbf683c893f1d7a5db54eb73c5b865 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::modal.close-button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::modal.close-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78bbf683c893f1d7a5db54eb73c5b865)): ?>
<?php $attributes = $__attributesOriginal78bbf683c893f1d7a5db54eb73c5b865; ?>
<?php unset($__attributesOriginal78bbf683c893f1d7a5db54eb73c5b865); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78bbf683c893f1d7a5db54eb73c5b865)): ?>
<?php $component = $__componentOriginal78bbf683c893f1d7a5db54eb73c5b865; ?>
<?php unset($__componentOriginal78bbf683c893f1d7a5db54eb73c5b865); ?>
<?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if($type): ?>
                <div class="modal-status bg-<?php echo e($type); ?>"></div>
            <?php endif; ?>

            <?php if($slot->isNotEmpty()): ?>
                <div <?php echo Html::attributes(array_merge($bodyAttrs, ['class' => rtrim('modal-body ' . Arr::get($bodyAttrs, 'class'))])); ?>>
                    <?php echo e($slot); ?>

                </div>
            <?php else: ?>
                <?php echo e($slot); ?>

            <?php endif; ?>

            <?php if(!empty($footer) && $footer->isNotEmpty()): ?>
                <div class="modal-footer">
                    <?php echo e($footer); ?>

                </div>
            <?php endif; ?>

            <?php if($buttonId && $buttonLabel): ?>
                <div class="modal-footer">
                    <?php if (isset($component)) { $__componentOriginal922f7d3260a518f4cf606eecf9669dcb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal922f7d3260a518f4cf606eecf9669dcb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::button','data' => ['type' => 'button','dataBsDismiss' => 'modal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','data-bs-dismiss' => 'modal']); ?>
                        <?php echo e(trans('core/base::tables.cancel')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal922f7d3260a518f4cf606eecf9669dcb)): ?>
<?php $attributes = $__attributesOriginal922f7d3260a518f4cf606eecf9669dcb; ?>
<?php unset($__attributesOriginal922f7d3260a518f4cf606eecf9669dcb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal922f7d3260a518f4cf606eecf9669dcb)): ?>
<?php $component = $__componentOriginal922f7d3260a518f4cf606eecf9669dcb; ?>
<?php unset($__componentOriginal922f7d3260a518f4cf606eecf9669dcb); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal922f7d3260a518f4cf606eecf9669dcb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal922f7d3260a518f4cf606eecf9669dcb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::button','data' => ['id' => $buttonId,'class' => \Illuminate\Support\Arr::toCssClasses(['ms-auto', $buttonClass => $buttonClass]),'color' => $type ?? 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($buttonId),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssClasses(['ms-auto', $buttonClass => $buttonClass])),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($type ?? 'primary')]); ?>
                        <?php echo e($buttonLabel); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal922f7d3260a518f4cf606eecf9669dcb)): ?>
<?php $attributes = $__attributesOriginal922f7d3260a518f4cf606eecf9669dcb; ?>
<?php unset($__attributesOriginal922f7d3260a518f4cf606eecf9669dcb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal922f7d3260a518f4cf606eecf9669dcb)): ?>
<?php $component = $__componentOriginal922f7d3260a518f4cf606eecf9669dcb; ?>
<?php unset($__componentOriginal922f7d3260a518f4cf606eecf9669dcb); ?>
<?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if($hasForm): ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/components/modal.blade.php ENDPATH**/ ?>