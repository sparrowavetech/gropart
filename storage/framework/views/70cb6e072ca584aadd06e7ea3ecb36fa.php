<?php
    $languages ??= Language::getActiveLanguage();
?>

<div
    class="text-end d-flex gap-2 justify-content-start justify-content-lg-end align-items-center list-languages-chooser">
    <h4 class="mb-0"><?php echo e(trans('plugins/language::language.translations')); ?>:</h4>
    <?php if(count($languages) <= 3): ?>
        <div class="d-flex gap-3 align-items-center">
            <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($language->lang_code === Language::getCurrentAdminLocaleCode()) continue; ?>

                <a
                    href="<?php echo e(route(Route::currentRouteName(), array_merge($params ?? [], $language->lang_code === Language::getDefaultLocaleCode() ? [] : [Language::refLangKey() => $language->lang_code]))); ?>"
                    class="text-decoration-none small"
                >
                    <?php echo language_flag($language->lang_flag, $language->lang_name); ?>

                    <?php echo e($language->lang_name); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginalf8303636a16ac3e808e27fabe59149a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8303636a16ac3e808e27fabe59149a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::dropdown.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
             <?php $__env->slot('trigger', null, []); ?> 
                <a
                    class="d-flex align-items-center gap-2 dropdown-toggle text-muted text-decoration-none"
                    href="#"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                >
                    <?php echo language_flag(
                        Arr::get(Language::getCurrentAdminLanguage(), 'lang_flag'),
                        Arr::get(Language::getCurrentAdminLanguage(), 'lang_name'),
                    ); ?>

                    <?php echo e(Arr::get(Language::getCurrentAdminLanguage(), 'lang_name')); ?>

                </a>
             <?php $__env->endSlot(); ?>

            <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($language->lang_code === Language::getCurrentAdminLocaleCode()) continue; ?>

                <?php if (isset($component)) { $__componentOriginal7681c9e8cd9d4250104639dd6412633f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7681c9e8cd9d4250104639dd6412633f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => '8def1252668913628243c4d363bee1ef::dropdown.item','data' => ['href' => route(
                        Route::currentRouteName(),
                        array_merge(
                            $params ?? [],
                            $language->lang_code === Language::getDefaultLocaleCode()
                                ? []
                                : [Language::refLangKey() => $language->lang_code],
                        ),
                    ),'class' => 'd-flex gap-2 align-items-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::dropdown.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route(
                        Route::currentRouteName(),
                        array_merge(
                            $params ?? [],
                            $language->lang_code === Language::getDefaultLocaleCode()
                                ? []
                                : [Language::refLangKey() => $language->lang_code],
                        ),
                    )),'class' => 'd-flex gap-2 align-items-center']); ?>
                    <?php echo language_flag($language->lang_flag, $language->lang_name); ?>

                    <?php echo e($language->lang_name); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7681c9e8cd9d4250104639dd6412633f)): ?>
<?php $attributes = $__attributesOriginal7681c9e8cd9d4250104639dd6412633f; ?>
<?php unset($__attributesOriginal7681c9e8cd9d4250104639dd6412633f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7681c9e8cd9d4250104639dd6412633f)): ?>
<?php $component = $__componentOriginal7681c9e8cd9d4250104639dd6412633f; ?>
<?php unset($__componentOriginal7681c9e8cd9d4250104639dd6412633f); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8303636a16ac3e808e27fabe59149a5)): ?>
<?php $attributes = $__attributesOriginalf8303636a16ac3e808e27fabe59149a5; ?>
<?php unset($__attributesOriginalf8303636a16ac3e808e27fabe59149a5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8303636a16ac3e808e27fabe59149a5)): ?>
<?php $component = $__componentOriginalf8303636a16ac3e808e27fabe59149a5; ?>
<?php unset($__componentOriginalf8303636a16ac3e808e27fabe59149a5); ?>
<?php endif; ?>
    <?php endif; ?>
    <input
        name="<?php echo e(Language::refLangKey()); ?>"
        type="hidden"
        value="<?php echo e(Language::getRefLang()); ?>"
    >
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/language/resources/views/partials/admin-list-language-chooser.blade.php ENDPATH**/ ?>