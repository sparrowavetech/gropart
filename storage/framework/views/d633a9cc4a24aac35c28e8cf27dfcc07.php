<?php if(Auth::user()->isImpersonated()): ?>
    <li class="dropdown">
        <a class="dropdown-toggle dropdown-header-name" style="padding-right: 10px"
           href="<?php echo e(route('users.leave_impersonation')); ?>">
            <i class="fas fa-user-ninja" style="color: #e7505a;"></i>
            <span class="d-none d-sm-inline"
                  style="color: #e7505a;"><?php echo e(trans('plugins/impersonate::impersonate.leave_impersonation')); ?></span>
        </a>
    </li>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/impersonate/resources/views/leave-impersonate.blade.php ENDPATH**/ ?>