<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title"><?php echo e(trans('plugins/ecommerce-wholesale::wholesale.wholesale_products')); ?></h4>
        </div>
        <div class="card-body">
            <?php if(is_plugin_active('marketplace')): ?>
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($tab === 'all' ? 'active' : ''); ?>"
                           href="<?php echo e(route('wholesale.products.index', ['tab' => 'all'])); ?>">
                            <?php echo e(trans('plugins/ecommerce-wholesale::wholesale.products.all')); ?>

                            <span class="badge bg-azure text-azure-fg ms-1"><?php echo e($inhouseCount + $sellerCount); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($tab === 'inhouse' ? 'active' : ''); ?>"
                           href="<?php echo e(route('wholesale.products.index', ['tab' => 'inhouse'])); ?>">
                            <?php echo e(trans('plugins/ecommerce-wholesale::wholesale.products.inhouse')); ?>

                            <span class="badge bg-blue text-blue-fg ms-1"><?php echo e($inhouseCount); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($tab === 'seller' ? 'active' : ''); ?>"
                           href="<?php echo e(route('wholesale.products.index', ['tab' => 'seller'])); ?>">
                            <?php echo e(trans('plugins/ecommerce-wholesale::wholesale.products.seller')); ?>

                            <span class="badge bg-teal text-teal-fg ms-1"><?php echo e($sellerCount); ?></span>
                        </a>
                    </li>
                </ul>
            <?php endif; ?>

            <?php if($products->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 60px;"></th>
                                <th><?php echo e(trans('core/base::tables.name')); ?></th>
                                <th><?php echo e(trans('plugins/ecommerce-wholesale::wholesale.products.sku')); ?></th>
                                <th><?php echo e(trans('plugins/ecommerce-wholesale::wholesale.products.pricing_tiers')); ?></th>
                                <th style="width: 100px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo e(RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage())); ?>"
                                             alt="<?php echo e($product->name); ?>"
                                             class="img-thumbnail"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <strong><?php echo e($product->name); ?></strong>
                                        <?php if(is_plugin_active('marketplace') && $product->store_id): ?>
                                            <br>
                                            <small class="text-info">
                                                <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-building-store'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                                                <?php echo e($product->store?->name ?? 'Seller'); ?>

                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo e($product->sku ?: 'N/A'); ?></span>
                                    </td>
                                    <td>
                                        <?php $__currentLoopData = $product->groupPricingRules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="badge bg-cyan text-cyan-fg me-1 mb-1">
                                                <?php echo e($rule->min_quantity); ?>-<?php echo e($rule->max_quantity ?? '∞'); ?>:
                                                <?php if($rule->discount_type->getValue() === 'percentage'): ?>
                                                    <?php echo e($rule->discount_value); ?>% off
                                                <?php elseif($rule->discount_type->getValue() === 'fixed'): ?>
                                                    -<?php echo e(format_price($rule->discount_value)); ?>

                                                <?php else: ?>
                                                    <?php echo e(format_price($rule->discount_value)); ?>

                                                <?php endif; ?>
                                                <?php if($rule->customerGroup): ?>
                                                    <small>(<?php echo e($rule->customerGroup->name); ?>)</small>
                                                <?php endif; ?>
                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo e(route('products.edit', $product->id)); ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-edit'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <?php echo e($products->appends(['tab' => $tab])->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-package-off'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['style' => 'font-size: 48px; color: #ccc;']); ?>
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
                    <p class="mt-3 text-muted"><?php echo e(trans('plugins/ecommerce-wholesale::wholesale.products.no_products')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(BaseHelper::getAdminMasterLayoutTemplate(), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/ecommerce-wholesale/resources/views/admin/wholesale-products.blade.php ENDPATH**/ ?>