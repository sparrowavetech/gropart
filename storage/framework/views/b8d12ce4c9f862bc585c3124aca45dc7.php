<div
    class="debug-badge"
    role="button"
    data-bs-toggle="modal"
    data-bs-target="#debug-mode-modal"
><?php echo e(trans('core/base::system.debug_mode_badge')); ?></div>

<?php if (isset($component)) { $__componentOriginal9376784f974ff66f3ff18195ab0a89c5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9376784f974ff66f3ff18195ab0a89c5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::modal.action','data' => ['id' => 'debug-mode-modal','type' => 'info','title' => trans('core/base::system.debug_mode_badge'),'size' => 'md','submitButtonLabel' => trans('core/base::system.fix_it_for_me'),'submitButtonAttrs' => ['data-bs-toggle' => 'modal', 'data-bs-target' => '#debug-mode-turn-off-confirmation-modal'],'submitButtonColor' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::modal.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'debug-mode-modal','type' => 'info','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(trans('core/base::system.debug_mode_badge')),'size' => 'md','submit-button-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(trans('core/base::system.fix_it_for_me')),'submit-button-attrs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['data-bs-toggle' => 'modal', 'data-bs-target' => '#debug-mode-turn-off-confirmation-modal']),'submit-button-color' => 'warning']); ?>
    <div class="text-start">
        <p>
            <?php echo BaseHelper::clean(trans('core/base::system.debug_mode_description_1')); ?>

        </p>
        <p>
            <?php echo BaseHelper::clean(trans('core/base::system.debug_mode_description_2')); ?>

        </p>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9376784f974ff66f3ff18195ab0a89c5)): ?>
<?php $attributes = $__attributesOriginal9376784f974ff66f3ff18195ab0a89c5; ?>
<?php unset($__attributesOriginal9376784f974ff66f3ff18195ab0a89c5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9376784f974ff66f3ff18195ab0a89c5)): ?>
<?php $component = $__componentOriginal9376784f974ff66f3ff18195ab0a89c5; ?>
<?php unset($__componentOriginal9376784f974ff66f3ff18195ab0a89c5); ?>
<?php endif; ?>

<?php if (isset($component)) { $__componentOriginal9376784f974ff66f3ff18195ab0a89c5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9376784f974ff66f3ff18195ab0a89c5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::modal.action','data' => ['id' => 'debug-mode-turn-off-confirmation-modal','type' => 'warning','title' => trans('core/base::system.are_you_sure'),'description' => trans('core/base::system.turn_off_debug_confirmation'),'submitButtonLabel' => trans('core/base::system.yes_turn_off'),'submitButtonAttrs' => ['id' => 'debug-mode-turn-off-form-submit', 'data-url' => route('system.debug-mode.turn-off')],'cancelButton' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::modal.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'debug-mode-turn-off-confirmation-modal','type' => 'warning','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(trans('core/base::system.are_you_sure')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(trans('core/base::system.turn_off_debug_confirmation')),'submit-button-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(trans('core/base::system.yes_turn_off')),'submit-button-attrs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['id' => 'debug-mode-turn-off-form-submit', 'data-url' => route('system.debug-mode.turn-off')]),'cancel-button' => true]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9376784f974ff66f3ff18195ab0a89c5)): ?>
<?php $attributes = $__attributesOriginal9376784f974ff66f3ff18195ab0a89c5; ?>
<?php unset($__attributesOriginal9376784f974ff66f3ff18195ab0a89c5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9376784f974ff66f3ff18195ab0a89c5)): ?>
<?php $component = $__componentOriginal9376784f974ff66f3ff18195ab0a89c5; ?>
<?php unset($__componentOriginal9376784f974ff66f3ff18195ab0a89c5); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/base/resources/views/components/debug-badge.blade.php ENDPATH**/ ?>