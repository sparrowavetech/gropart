<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => null,
    'type' => 'info',
    'title' => null,
    'description' => null,
    'icon' => null,
    'submitButtonLabel' => 'Submit',
    'submitButtonColor' => null,
    'submitButtonAttrs' => [],
    'closeButtonLabel' => trans('core/base::base.close'),
    'closeButtonColor' => null,
    'formAction' => null,
    'formMethod' => 'POST',
    'formAttrs' => [],
    'hasForm' => false,
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
    'type' => 'info',
    'title' => null,
    'description' => null,
    'icon' => null,
    'submitButtonLabel' => 'Submit',
    'submitButtonColor' => null,
    'submitButtonAttrs' => [],
    'closeButtonLabel' => trans('core/base::base.close'),
    'closeButtonColor' => null,
    'formAction' => null,
    'formMethod' => 'POST',
    'formAttrs' => [],
    'hasForm' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $submitButtonColor ??= $type;
?>

<?php if (isset($component)) { $__componentOriginalf0bc7448d2f05d41114d69bcb7cd7a1f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0bc7448d2f05d41114d69bcb7cd7a1f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::modal.alert','data' => ['id' => $id,'type' => $type,'title' => $title,'icon' => $icon,'attributes' => $attributes->merge(['size' => 'sm', 'closeButton' => false]),'bodyAttrs' => ['class' => 'text-center py-4'],'formAction' => $formAction,'formMethod' => $formMethod,'formAttrs' => $formAttrs,'hasForm' => $hasForm]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::modal.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($id),'type' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($type),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->merge(['size' => 'sm', 'closeButton' => false])),'body-attrs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['class' => 'text-center py-4']),'form-action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($formAction),'form-method' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($formMethod),'form-attrs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($formAttrs),'has-form' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasForm)]); ?>
    <?php if(!empty($description)): ?>
        <div class="text-muted text-break">
            <?php echo $description; ?>

        </div>
    <?php else: ?>
        <?php echo e($slot); ?>

    <?php endif; ?>

     <?php $__env->slot('footer', null, []); ?> 
        <div class="w-100">
            <div class="row">
                <?php if(!isset($footer)): ?>
                    <div class="col">
                        <button
                            type="button"
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'w-100 btn',
                                "btn-$submitButtonColor",
                                Arr::get($submitButtonAttrs, 'class'),
                            ]); ?>"
                            <?php echo Html::attributes(Arr::except($submitButtonAttrs, 'class')); ?>

                        >
                            <?php echo e($submitButtonLabel); ?>

                        </button>
                    </div>
                    <div class="col">
                        <button
                            type="button"
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses(['w-100 btn', "btn-$closeButtonColor" => !$closeButtonColor]); ?>"
                            data-bs-dismiss="modal"
                        >
                            <?php echo e($closeButtonLabel); ?>

                        </button>
                    </div>
                <?php else: ?>
                    <?php echo e($footer); ?>

                <?php endif; ?>
            </div>
        </div>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0bc7448d2f05d41114d69bcb7cd7a1f)): ?>
<?php $attributes = $__attributesOriginalf0bc7448d2f05d41114d69bcb7cd7a1f; ?>
<?php unset($__attributesOriginalf0bc7448d2f05d41114d69bcb7cd7a1f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0bc7448d2f05d41114d69bcb7cd7a1f)): ?>
<?php $component = $__componentOriginalf0bc7448d2f05d41114d69bcb7cd7a1f; ?>
<?php unset($__componentOriginalf0bc7448d2f05d41114d69bcb7cd7a1f); ?>
<?php endif; ?>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/components/modal/action.blade.php ENDPATH**/ ?>