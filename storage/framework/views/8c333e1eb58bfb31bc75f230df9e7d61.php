<nav id="admin_bar">
    <div class="admin-bar-container">
        <div class="admin-bar-logo">
            <a
                href="<?php echo e(route('dashboard.index')); ?>"
                title="<?php echo e(trans('packages/theme::theme.go_to_dashboard')); ?>"
            >
                <img
                    src="<?php echo e(setting('admin_logo') ? RvMedia::getImageUrl(setting('admin_logo')) : url(config('core.base.general.logo'))); ?>"
                    alt="logo"
                />
            </a>
        </div>
        <ul class="admin-navbar-nav">
            <?php $__currentLoopData = AdminBar::getGroups(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slug => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($items = Arr::get($group, 'items', [])): ?>
                    <?php ksort($items); ?>
                    <li class="admin-bar-dropdown">
                        <a href="<?php echo e(Arr::get($group, 'link')); ?>">
                            <?php echo e(Arr::get($group, 'title')); ?>

                        </a>
                        <ul class="admin-bar-dropdown-menu">
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(Arr::get($item, 'permission') && !Auth::guard()->user()->hasPermission($item['permission'])) continue; ?>
                                <li>
                                    <a href="<?php echo e(Arr::get($item, 'link')); ?>">
                                        <?php echo e(Arr::get($item, 'title')); ?>

                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </li>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($noGroups = AdminBar::getLinksNoGroup()): ?>
                <?php ksort($noGroups) ?>
                <?php $__currentLoopData = $noGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(Arr::get($item, 'permission') && !Auth::guard()->user()->hasPermission($item['permission'])) continue; ?>
                    <li>
                        <a href="<?php echo e(Arr::get($item, 'link')); ?>"><?php echo e(Arr::get($item, 'title')); ?></a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </ul>
        <ul class="admin-navbar-nav admin-navbar-nav-right">
            <li class="admin-bar-dropdown">
                <a href="<?php echo e(Auth::guard()->user()->url); ?>">
                    <?php echo e(Auth::guard()->user()->name); ?>

                </a>
                <ul class="admin-bar-dropdown-menu">
                    <li><a href="<?php echo e(Auth::guard()->user()->url); ?>"><?php echo e(trans('core/base::layouts.profile')); ?></a>
                    </li>
                    <li><a href="<?php echo e(route('access.logout')); ?>"><?php echo e(trans('core/base::layouts.logout')); ?></a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
<script type="text/javascript">
    document.getElementsByTagName('body')[0].classList.add('show-admin-bar');
</script>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/packages/theme/resources/views/admin-bar.blade.php ENDPATH**/ ?>