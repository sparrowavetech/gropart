<?php use Botble\Base\Facades\Assets; ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => null,
    'label' => null,
    'name' => null,
    'value' => old($name),
    'helperText' => null,
    'errorKey' => $name,
    'mode' => null,
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
    'value' => old($name),
    'helperText' => null,
    'errorKey' => $name,
    'mode' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $id = $id ?: $name . '_' . md5($name);
    $mode = $mode === 'html' ? 'htmlmixed' : $mode;

    $css = [
        'vendor/core/core/base/libraries/codemirror/lib/codemirror.css',
        'vendor/core/core/base/libraries/codemirror/addon/hint/show-hint.css',
    ];

    $js = [
        'vendor/core/core/base/libraries/codemirror/lib/codemirror.js',
        'vendor/core/core/base/libraries/codemirror/addon/hint/show-hint.js',
        'vendor/core/core/base/libraries/codemirror/addon/hint/anyword-hint.js',
        'vendor/core/core/base/libraries/codemirror/addon/display/autorefresh.js',
    ];

    switch ($mode) {
        case 'htmlmixed':
            $js = [
                ...$js,
                'vendor/core/core/base/libraries/codemirror/mode/htmlmixed.js',
                'vendor/core/core/base/libraries/codemirror/mode/css.js',
                'vendor/core/core/base/libraries/codemirror/mode/javascript.js',
                'vendor/core/core/base/libraries/codemirror/mode/xml.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/xml-hint.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/html-hint.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/css-hint.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/javascript-hint.js',
            ];

            break;

        case 'css':
            $js = [
                ...$js,
                'vendor/core/core/base/libraries/codemirror/mode/css.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/css-hint.js',
            ];

            break;

        case 'javascript':
            $js = [
                ...$js,
                'vendor/core/core/base/libraries/codemirror/mode/javascript.js',
                'vendor/core/core/base/libraries/codemirror/addon/hint/javascript-hint.js',
            ];

            break;
    }

    Assets::addStylesDirectly($css)->addScriptsDirectly($js);
?>

<?php if (isset($component)) { $__componentOriginala0a922bb70d8e2bee74cdab0a323562a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala0a922bb70d8e2bee74cdab0a323562a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::form-group','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::form-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if($label): ?>
        <?php if (isset($component)) { $__componentOriginal50e5e771b30c35423d2b4f118feb7c0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal50e5e771b30c35423d2b4f118feb7c0c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::form.label','data' => ['label' => $label,'for' => $id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::form.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($id)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal50e5e771b30c35423d2b4f118feb7c0c)): ?>
<?php $attributes = $__attributesOriginal50e5e771b30c35423d2b4f118feb7c0c; ?>
<?php unset($__attributesOriginal50e5e771b30c35423d2b4f118feb7c0c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal50e5e771b30c35423d2b4f118feb7c0c)): ?>
<?php $component = $__componentOriginal50e5e771b30c35423d2b4f118feb7c0c; ?>
<?php unset($__componentOriginal50e5e771b30c35423d2b4f118feb7c0c); ?>
<?php endif; ?>
    <?php endif; ?>

    <textarea
        <?php echo e($attributes->merge(['name' => $name, 'class' => 'form-control', 'id' => $id, 'data-bb-code-editor' => '', 'data-mode' => $mode])); ?>

    ><?php echo e($value ?: $slot); ?></textarea>

    <?php if($helperText): ?>
        <?php if (isset($component)) { $__componentOriginal1844d57dc6206b688bd5adc7dea47e7d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1844d57dc6206b688bd5adc7dea47e7d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::form.helper-text','data' => ['class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::form.helper-text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-2']); ?><?php echo $helperText; ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1844d57dc6206b688bd5adc7dea47e7d)): ?>
<?php $attributes = $__attributesOriginal1844d57dc6206b688bd5adc7dea47e7d; ?>
<?php unset($__attributesOriginal1844d57dc6206b688bd5adc7dea47e7d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1844d57dc6206b688bd5adc7dea47e7d)): ?>
<?php $component = $__componentOriginal1844d57dc6206b688bd5adc7dea47e7d; ?>
<?php unset($__componentOriginal1844d57dc6206b688bd5adc7dea47e7d); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal5eeffca643f98617c0ca70ab61dd7dad = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5eeffca643f98617c0ca70ab61dd7dad = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::form.error','data' => ['key' => $errorKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::form.error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errorKey)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5eeffca643f98617c0ca70ab61dd7dad)): ?>
<?php $attributes = $__attributesOriginal5eeffca643f98617c0ca70ab61dd7dad; ?>
<?php unset($__attributesOriginal5eeffca643f98617c0ca70ab61dd7dad); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5eeffca643f98617c0ca70ab61dd7dad)): ?>
<?php $component = $__componentOriginal5eeffca643f98617c0ca70ab61dd7dad; ?>
<?php unset($__componentOriginal5eeffca643f98617c0ca70ab61dd7dad); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala0a922bb70d8e2bee74cdab0a323562a)): ?>
<?php $attributes = $__attributesOriginala0a922bb70d8e2bee74cdab0a323562a; ?>
<?php unset($__attributesOriginala0a922bb70d8e2bee74cdab0a323562a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala0a922bb70d8e2bee74cdab0a323562a)): ?>
<?php $component = $__componentOriginala0a922bb70d8e2bee74cdab0a323562a; ?>
<?php unset($__componentOriginala0a922bb70d8e2bee74cdab0a323562a); ?>
<?php endif; ?>

<?php if(request()->ajax()): ?>
    <?php $__currentLoopData = $css; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cssItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <link
            rel="stylesheet"
            href="<?php echo e(asset($cssItem)); ?>"
        >
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php $__currentLoopData = $js; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jsItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <script src="<?php echo e(asset($jsItem)); ?>"></script>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/components/form/code-editor.blade.php ENDPATH**/ ?>