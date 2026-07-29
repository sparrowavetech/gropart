<?php
    $manageLicense = auth()->user()->hasPermission('core.manage.license');

    $licenseTitle = trans('core/setting::setting.license_title');
    $licenseDescription = trans('core/setting::setting.setup_license');
?>

<v-license-form
    id="license-form"
    verify-url="<?php echo e(route('settings.license.verify.index')); ?>"
    activate-license-url="<?php echo e(route('settings.license.activate')); ?>"
    deactivate-license-url="<?php echo e(route('settings.license.deactivate')); ?>"
    reset-license-url="<?php echo e(route('settings.license.reset')); ?>"
    v-slot="{ initialized, loading, verified, license, deactivateLicense, resetLicense }"
    v-cloak
>
    <template v-if="initialized && (! verified || ! license)">
        <?php if (isset($component)) { $__componentOriginal0a39fdd16f40824788edeed4e460894f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0a39fdd16f40824788edeed4e460894f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'cbdd3a194c072fbc3f04e785bd9366eb::section','data' => ['title' => $licenseTitle,'description' => $licenseDescription]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core-setting::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($licenseTitle),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($licenseDescription)]); ?>
            <?php if (isset($component)) { $__componentOriginald34a25f8ff9d26735e5b798f732d2b5d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald34a25f8ff9d26735e5b798f732d2b5d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::license.form','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::license.form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald34a25f8ff9d26735e5b798f732d2b5d)): ?>
<?php $attributes = $__attributesOriginald34a25f8ff9d26735e5b798f732d2b5d; ?>
<?php unset($__attributesOriginald34a25f8ff9d26735e5b798f732d2b5d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald34a25f8ff9d26735e5b798f732d2b5d)): ?>
<?php $component = $__componentOriginald34a25f8ff9d26735e5b798f732d2b5d; ?>
<?php unset($__componentOriginald34a25f8ff9d26735e5b798f732d2b5d); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal7edd4d0d68777cdefae7087b439de89f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7edd4d0d68777cdefae7087b439de89f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::loading','data' => ['vIf' => 'loading']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::loading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['v-if' => 'loading']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7edd4d0d68777cdefae7087b439de89f)): ?>
<?php $attributes = $__attributesOriginal7edd4d0d68777cdefae7087b439de89f; ?>
<?php unset($__attributesOriginal7edd4d0d68777cdefae7087b439de89f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7edd4d0d68777cdefae7087b439de89f)): ?>
<?php $component = $__componentOriginal7edd4d0d68777cdefae7087b439de89f; ?>
<?php unset($__componentOriginal7edd4d0d68777cdefae7087b439de89f); ?>
<?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0a39fdd16f40824788edeed4e460894f)): ?>
<?php $attributes = $__attributesOriginal0a39fdd16f40824788edeed4e460894f; ?>
<?php unset($__attributesOriginal0a39fdd16f40824788edeed4e460894f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0a39fdd16f40824788edeed4e460894f)): ?>
<?php $component = $__componentOriginal0a39fdd16f40824788edeed4e460894f; ?>
<?php unset($__componentOriginal0a39fdd16f40824788edeed4e460894f); ?>
<?php endif; ?>
    </template>

    <template v-if="initialized && verified && license">
        <?php if(!config('core.base.general.hide_activated_license_info', false)): ?>
            <?php if (isset($component)) { $__componentOriginal0a39fdd16f40824788edeed4e460894f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0a39fdd16f40824788edeed4e460894f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'cbdd3a194c072fbc3f04e785bd9366eb::section','data' => ['title' => $licenseTitle,'description' => $licenseDescription]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core-setting::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($licenseTitle),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($licenseDescription)]); ?>
                <p class="text-info">
                    <span v-if="license.licensed_to">Licensed to <span v-text="license.licensed_to"></span>.
                    </span>Activated
                    since <span v-text="license.activated_at"></span>.
                </p>

                <div>
                    <?php if (isset($component)) { $__componentOriginal922f7d3260a518f4cf606eecf9669dcb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal922f7d3260a518f4cf606eecf9669dcb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::button','data' => ['color' => 'warning','icon' => 'ti ti-x','@click' => 'deactivateLicense','vBind:disabled' => 'loading || '.e(!$manageLicense ? 'true' : 'false').'','vBind:class' => '{ \'btn-loading\': loading }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'warning','icon' => 'ti ti-x','@click' => 'deactivateLicense','v-bind:disabled' => 'loading || '.e(!$manageLicense ? 'true' : 'false').'','v-bind:class' => '{ \'btn-loading\': loading }']); ?>
                        Deactivate license
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
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0a39fdd16f40824788edeed4e460894f)): ?>
<?php $attributes = $__attributesOriginal0a39fdd16f40824788edeed4e460894f; ?>
<?php unset($__attributesOriginal0a39fdd16f40824788edeed4e460894f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0a39fdd16f40824788edeed4e460894f)): ?>
<?php $component = $__componentOriginal0a39fdd16f40824788edeed4e460894f; ?>
<?php unset($__componentOriginal0a39fdd16f40824788edeed4e460894f); ?>
<?php endif; ?>
        <?php endif; ?>
    </template>
</v-license-form>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/core/setting/resources/views/partials/license.blade.php ENDPATH**/ ?>