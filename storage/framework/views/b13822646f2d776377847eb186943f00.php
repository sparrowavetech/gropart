<div class="star-rating-wrapper">
    <div
        class="star-rating"
        role="img"
        aria-label="Rated <?php echo e($avg); ?> out of 5"
    >
        <span class="max-rating rating-stars">
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-star"
                        xlink:href="#svg-icon-star"
                    ></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-star"
                        xlink:href="#svg-icon-star"
                    ></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-star"
                        xlink:href="#svg-icon-star"
                    ></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-star"
                        xlink:href="#svg-icon-star"
                    ></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-star"
                        xlink:href="#svg-icon-star"
                    ></use>
                </svg>
            </span>
        </span>
        <span
            class="user-rating rating-stars"
            style="width: <?php echo e($avg * 20); ?>%"
        >
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-star"
                        xlink:href="#svg-icon-star"
                    ></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-star"
                        xlink:href="#svg-icon-star"
                    ></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-star"
                        xlink:href="#svg-icon-star"
                    ></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-star"
                        xlink:href="#svg-icon-star"
                    ></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use
                        href="#svg-icon-star"
                        xlink:href="#svg-icon-star"
                    ></use>
                </svg>
            </span>
        </span>
    </div>
    <?php if(isset($count)): ?>
        <small class="star-count ms-1 text-secondary d-inline-block">
            (<span class="d-inline-block"><?php echo e($count); ?></span>)
        </small>
    <?php endif; ?>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/themes/farmart/partials/star-rating.blade.php ENDPATH**/ ?>