<?php
    $name = Str::contains($name = $menu['name'], '::') ? BaseHelper::clean(trans($name)) : $name;
?>
<a
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'nav-link' => ($isNav = $isNav ?? true),
        'dropdown-item' => !$isNav,
        'dropdown-toggle' => $hasChildren,
        'nav-priority-' . $menu['priority'],
        'active show' => $menu['active'],
    ]); ?>"
    href="<?php echo e($hasChildren ? "#$menu[id]" : $menu['url']); ?>"
    id="<?php echo e($menu['id']); ?>"
    <?php if($hasChildren): ?> data-bs-toggle="dropdown"
        data-bs-auto-close="<?php echo e($autoClose ?? 'false'); ?>"
        role="button"
        aria-expanded="<?php echo e($menu['active'] ? 'true' : 'false'); ?>" <?php endif; ?>
    title="<?php echo e($menu['title'] ?? $name); ?>"
>
    <?php if(AdminAppearance::showMenuItemIcon() && $menu['icon'] !== false): ?>
        <span
            class="nav-link-icon d-md-none d-lg-inline-block"
            title="<?php echo e($name); ?>"
        >
            <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => $menu['icon'] ?: 'ti ti-point'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
        </span>
    <?php endif; ?>

    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['nav-link-title text-truncate']); ?>">
        <?php echo $name; ?>

        <?php echo apply_filters(BASE_FILTER_APPEND_MENU_NAME, null, $menu['id']); ?>

    </span>
</a>
<?php /**PATH D:\xampp82\htdocs\gropart\platform\core\base\/resources/views/layouts/partials/navbar-nav-item-link.blade.php ENDPATH**/ ?>