<?php
    $supportedLocales = Language::getSupportedLocales();
    if (empty($options)) {
        $options = [
            'before' => '',
            'lang_flag' => true,
            'lang_name' => true,
            'class' => '',
            'after' => '',
        ];
    }
?>

<?php if($supportedLocales && count($supportedLocales) > 1): ?>
    <?php
        $languageDisplay = setting('language_display', 'all');
    ?>
    <?php if(setting('language_switcher_display', 'dropdown') == 'dropdown'): ?>
        <li>
            <?php echo Arr::get($options, 'before'); ?>

            <span class="language-dropdown-active">
                <?php if(Arr::get($options, 'lang_flag', true) && ($languageDisplay == 'all' || $languageDisplay == 'flag')): ?>
                    <?php echo language_flag(Language::getCurrentLocaleFlag(), Language::getCurrentLocaleName()); ?>

                <?php endif; ?>
                <?php if(Arr::get($options, 'lang_name', true) && ($languageDisplay == 'all' || $languageDisplay == 'name')): ?>
                    <?php echo e(Language::getCurrentLocaleName()); ?>

                <?php endif; ?>
                <span class="svg-icon">
                    <svg>
                        <use
                            href="#svg-icon-chevron-down"
                            xlink:href="#svg-icon-chevron-down"
                        ></use>
                    </svg>
                </span>
            </span>
            <ul class="language-dropdown <?php echo e(Arr::get($options, 'class')); ?>">
                <?php $__currentLoopData = $supportedLocales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $localeCode => $properties): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($localeCode != Language::getCurrentLocale()): ?>
                        <li>
                            <a href="<?php echo e(Language::getSwitcherUrl($localeCode, $properties['lang_code'])); ?>">
                                <?php if(Arr::get($options, 'lang_flag', true) && ($languageDisplay == 'all' || $languageDisplay == 'flag')): ?>
                                    <?php echo language_flag($properties['lang_flag'], $properties['lang_name']); ?>

                                <?php endif; ?>
                                <?php if(Arr::get($options, 'lang_name', true) && ($languageDisplay == 'all' || $languageDisplay == 'name')): ?>
                                    <span><?php echo e($properties['lang_name']); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <?php echo Arr::get($options, 'after'); ?>

        </li>
    <?php else: ?>
        <?php $__currentLoopData = $supportedLocales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $localeCode => $properties): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($localeCode != Language::getCurrentLocale()): ?>
                <li>
                    <a href="<?php echo e(Language::getSwitcherUrl($localeCode, $properties['lang_code'])); ?>">
                        <?php if(Arr::get($options, 'lang_flag', true) && ($languageDisplay == 'all' || $languageDisplay == 'flag')): ?>
                            <?php echo language_flag($properties['lang_flag'], $properties['lang_name']); ?>

                        <?php endif; ?>
                        <?php if(Arr::get($options, 'lang_name', true) && ($languageDisplay == 'all' || $languageDisplay == 'name')): ?>
                            &nbsp;<span><?php echo e($properties['lang_name']); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/language-switcher.blade.php ENDPATH**/ ?>