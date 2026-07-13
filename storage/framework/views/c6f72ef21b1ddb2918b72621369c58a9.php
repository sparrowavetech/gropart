<?php
    $groupedCategories = ProductCategoryHelper::getProductCategoriesWithUrl()->groupBy('parent_id');

    $hiddenCategoryIds = array_filter(array_map('intval', explode(',', theme_option('hidden_product_categories_in_dropdown', ''))));

    $currentCategories = $groupedCategories->get(0)?->reject(fn ($category) => in_array($category->id, $hiddenCategoryIds));
?>

<?php if($currentCategories): ?>
    <?php $__currentLoopData = $currentCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $hasChildren = $groupedCategories->has($category->id);
            $iconImage = $category->icon_image;
            $icon = $category->icon;
        ?>

        <li <?php if($hasChildren): ?> class="menu-item-has-children has-mega-menu" <?php endif; ?>>
            <a href="<?php echo e(route('public.single', $category->url)); ?>">
                <?php if($iconImage): ?>
                    <?php echo e(RvMedia::image($iconImage, __('Icon'), attributes: ['loading' => false, 'style' => 'width: 18px; height: 18px'])); ?>

                <?php elseif($icon): ?>
                    <?php echo BaseHelper::renderIcon($icon); ?>

                <?php endif; ?>
                <span class="ms-1"><?php echo e($category->name); ?></span>
                <?php if($hasChildren): ?>
                    <span class="sub-toggle">
                    <span class="svg-icon">
                        <svg>
                            <use
                                href="#svg-icon-chevron-right"
                                xlink:href="#svg-icon-chevron-right"
                            ></use>
                        </svg>
                    </span>
                </span>
                <?php endif; ?>
            </a>
            <?php if($hasChildren): ?>
                <?php
                    $currentCategories = $groupedCategories->get($category->id);
                ?>

                <div class="mega-menu" <?php if(! $groupedCategories->has($currentCategories[0]->id)): ?> style="min-width: 250px;" <?php endif; ?>>
                    <div class="mega-menu-wrapper">
                        <?php if($currentCategories): ?>
                            <?php $__currentLoopData = $currentCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $childCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $hasChildren = $groupedCategories->has($childCategory->id);
                                ?>
                                <div class="mega-menu__column">
                                    <?php if($hasChildren): ?>
                                        <a href="<?php echo e(route('public.single', $childCategory->url)); ?>">
                                            <h4>
                                                <?php if($childCategory->icon_image): ?>
                                                    <img
                                                        src="<?php echo e(RvMedia::getImageUrl($childCategory->icon_image)); ?>"
                                                        alt="<?php echo e($childCategory->name); ?>"
                                                        width="18"
                                                        height="18"
                                                        style="vertical-align: top;"
                                                    >
                                                <?php elseif($childCategory->icon): ?>
                                                    <i class="<?php echo e($childCategory->icon); ?>"></i>
                                                <?php endif; ?>
                                                <span class="ms-1"><?php echo e($childCategory->name); ?></span>
                                            </h4>
                                            <span class="sub-toggle">
                                        <span class="svg-icon">
                                            <svg>
                                                <use
                                                    href="#svg-icon-chevron-right"
                                                    xlink:href="#svg-icon-chevron-right"
                                                ></use>
                                            </svg>
                                        </span>
                                    </span>
                                        </a>
                                        <ul class="mega-menu__list">
                                            <?php
                                                $currentCategories = $groupedCategories->get($childCategory->id);
                                            ?>
                                            <?php if($currentCategories): ?>
                                                <?php $__currentLoopData = $currentCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li>
                                                        <a href="<?php echo e(route('public.single', $item->url)); ?>">
                                                            <?php if($item->icon_image): ?>
                                                                <img
                                                                    src="<?php echo e(RvMedia::getImageUrl($item->icon_image)); ?>"
                                                                    alt="<?php echo e($item->name); ?>"
                                                                    width="18"
                                                                    height="18"
                                                                    style="vertical-align: top;"
                                                                >
                                                            <?php elseif($item->icon): ?>
                                                                <i class="<?php echo e($item->icon); ?>"></i>
                                                            <?php endif; ?>
                                                            <span class="ms-1"><?php echo e($item->name); ?></span>
                                                        </a>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                        </ul>
                                    <?php else: ?>
                                        <a href="<?php echo e(route('public.single', $childCategory->url)); ?>"><?php echo e($childCategory->name); ?></a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/product-categories-dropdown.blade.php ENDPATH**/ ?>