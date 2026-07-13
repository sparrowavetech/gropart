<?php if($sidebar == 'footer_sidebar'): ?>
    <div class="col-xl-3">
        <div class="widget mb-5 mb-md-0">
            <p class="h5 fw-bold widget-title mb-4"><?php echo e($config['name']); ?></p>
            <div class="widget-description mb-4"><?php echo BaseHelper::clean($config['about']); ?></div>
            <ul class="ps-0 mt-3 mb-0">
                <?php if($config['phone']): ?>
                    <li class="py-2">
                        <span class="svg-icon me-2">
                            <svg>
                                <use
                                    href="#svg-icon-phone"
                                    xlink:href="#svg-icon-phone"
                                ></use>
                            </svg>
                        </span>
                        <span><?php echo e(__('Hotline 24/7:')); ?>

                            <p class="h4 ms-4 mt-2"><a href="tel:<?php echo e($config['phone']); ?>"><?php echo e($config['phone']); ?></a></p>
                        </span>
                    </li>
                <?php endif; ?>
                <?php if($config['address']): ?>
                    <li class="py-2">
                        <span class="svg-icon me-2">
                            <svg>
                                <use
                                    href="#svg-icon-home"
                                    xlink:href="#svg-icon-home"
                                ></use>
                            </svg>
                        </span>
                        <span><?php echo e($config['address']); ?></span>
                    </li>
                <?php endif; ?>
                <?php if($config['email']): ?>
                    <li class="py-2">
                        <span class="svg-icon me-2">
                            <svg>
                                <use
                                    href="#svg-icon-mail"
                                    xlink:href="#svg-icon-mail"
                                ></use>
                            </svg>
                        </span>
                        <span><a href="mailto:<?php echo e($config['email']); ?>"><?php echo e($config['email']); ?></a></span>
                    </li>
                <?php endif; ?>

                <?php if($config['working_time']): ?>
                    <li class="py-2">
                        <span class="me-2">
                            <i class="icon-clock3"></i>
                        </span>
                        <span><?php echo e($config['working_time']); ?></span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
<?php elseif($config['working_time'] || $config['phone']): ?>
    <div class="row bg-light mb-4 g-0">
        <div class="col-12">
            <div class="px-3 py-4">
                <h6 class="fw-bold"><?php echo e(__('Hotline Order')); ?>:</h6>
                <?php if($config['working_time']): ?>
                    <p class="text"><?php echo e($config['working_time']); ?></p>
                <?php endif; ?>
                <?php if($config['phone']): ?>
                    <h4 class="fw-bold"><?php echo e($config['phone']); ?></h4>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/////widgets/site-info/templates/frontend.blade.php ENDPATH**/ ?>