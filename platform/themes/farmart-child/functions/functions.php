<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Filter;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductCollection;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Shortcode\Compilers\Shortcode as ShortcodeCompiler;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Testimonial\Models\Testimonial;
use Botble\Theme\Events\RenderingTheme;
use Botble\Theme\Events\RenderingThemeOptionSettings;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Theme\Farmart\Supports\Wishlist;

app('events')->listen(RenderingThemeOptionSettings::class, function (): void {
    if (function_exists('theme_option')) {
        theme_option()
            ->setField([
                'id' => 'top_upper_header_text',
                'section_id' => 'opt-text-subsection-general',
                'type' => 'editor',
                'label' => __('Top Header CTA Text'),
                'attributes' => [
                    'name' => 'top_upper_header_text',
                    'value' => null,
                    'options' => [
                        'class' => 'form-control',
                        'placeholder' => 'Enter any Text line to add in top header',
                    ],
                ],
            ])
            ->setField([
                'id' => 'enabled_product_categories_sidebar_on_header',
                'section_id' => 'opt-text-subsection-ecommerce',
                'type' => 'customSelect',
                'label' => __('Enable categories with sidebar on header?'),
                'attributes' => [
                    'name' => 'enabled_product_categories_sidebar_on_header',
                    'list' => [
                        'yes' => trans('core/base::base.yes'),
                        'no' => trans('core/base::base.no'),
                    ],
                    'value' => 'yes',
                    'options' => [
                        'class' => 'form-control',
                    ],
                ],
            ]);
    }
});



// Register custom shortcodes on RouteMatched
app('events')->listen(RouteMatched::class, function (): void {
    if (is_plugin_active('ecommerce')) {
        add_shortcode('all-brands', __('All Brands'), __('All Brands'), function (ShortcodeCompiler $shortcode) {
            return Theme::partial('shortcodes.ecommerce.all-brands', [
                'shortcode' => $shortcode,
            ]);
        });

        add_shortcode('all-categories', __('All Categories'), __('All Categories'), function (ShortcodeCompiler $shortcode) {
            return Theme::partial('shortcodes.ecommerce.all-categories', [
                'shortcode' => $shortcode,
            ]);
        });

        add_shortcode('product-collections-full', __('Product Collections Full'), __('Product Collections Full'), function (ShortcodeCompiler $shortcode) {
            $collectionId = $shortcode->collection_id;
            if ($collectionId) {
                $collectionIds = is_array($collectionId) ? $collectionId : explode(',', (string) $collectionId);
            } else {
                $collectionIds = ProductCollection::query()
                    ->wherePublished()
                    ->pluck('id')
                    ->all();
            }

            $limit = (int) ($shortcode->limit ?: 100);

            $products = get_products_by_collections(array_merge([
                'collections' => [
                    'by' => 'id',
                    'value_in' => $collectionIds,
                ],
                'take' => $limit,
                'with' => EcommerceHelper::withProductEagerLoadingRelations(),
            ], EcommerceHelper::withReviewsParams()));

            if (! $products instanceof Collection) {
                $products = $products ? collect([$products]) : collect();
            }

            if ($products->isEmpty()) {
                return null;
            }

            $wishlistIds = class_exists(Wishlist::class) ? Wishlist::getWishlistIds($products->pluck('id')->all()) : [];

            return Theme::partial('shortcodes.ecommerce.product-collections-full', [
                'title' => $shortcode->title,
                'limit' => $limit,
                'shortcode' => $shortcode,
                'products' => $products,
                'wishlistIds' => $wishlistIds,
            ]);
        });

        add_shortcode('featured-products-full', __('Featured Products Full'), __('Featured Products Full'), function (ShortcodeCompiler $shortcode) {
            $limit = (int) ($shortcode->limit ?: 100);

            $products = get_featured_products(array_merge([
                'take' => $limit,
                'with' => EcommerceHelper::withProductEagerLoadingRelations(),
            ], EcommerceHelper::withReviewsParams()));

            if (! $products instanceof Collection) {
                $products = $products ? collect([$products]) : collect();
            }

            if ($products->isEmpty()) {
                return null;
            }

            $wishlistIds = class_exists(Wishlist::class) ? Wishlist::getWishlistIds($products->pluck('id')->all()) : [];

            return Theme::partial('shortcodes.ecommerce.featured-products-full', [
                'title' => $shortcode->title,
                'limit' => $limit,
                'shortcode' => $shortcode,
                'products' => $products,
                'wishlistIds' => $wishlistIds,
            ]);
        });

        add_shortcode('product-category-products-full', __('Product Category Products Full'), __('Product Category Products Full'), function (ShortcodeCompiler $shortcode) {
            $category = ProductCategory::query()
                ->wherePublished()
                ->where('id', $shortcode->category_id)
                ->first();

            if (! $category) {
                return null;
            }

            $limit = (int) ($shortcode->limit ?: 100);

            $products = app(ProductInterface::class)->getProductsByCategories(array_merge([
                'categories' => [
                    'by' => 'id',
                    'value_in' => array_merge([$category->id], $category->activeChildren->pluck('id')->all()),
                ],
                'take' => $limit,
                'with' => EcommerceHelper::withProductEagerLoadingRelations(),
            ], EcommerceHelper::withReviewsParams()));

            if (! $products instanceof Collection) {
                $products = $products ? collect([$products]) : collect();
            }

            if ($products->isEmpty()) {
                return null;
            }

            $wishlistIds = class_exists(Wishlist::class) ? Wishlist::getWishlistIds($products->pluck('id')->all()) : [];

            return Theme::partial('shortcodes.ecommerce.product-category-products-full', [
                'title' => $shortcode->title,
                'category' => $category,
                'limit' => $limit,
                'shortcode' => $shortcode,
                'products' => $products,
                'wishlistIds' => $wishlistIds,
            ]);
        });
    }

    add_shortcode('text-image-row', __('Text Image Row'), __('Text Image Row'), function (ShortcodeCompiler $shortcode) {
        return Theme::partial('shortcodes.text-image-row', [
            'shortcode' => $shortcode,
        ]);
    });

    // Shortcode content compiled filter for testimonials
    add_filter('shortcode_content_compiled', function ($html, $name, $callback = null, $compiler = null) {
        if ($name === 'testimonials' && (empty($html) || trim((string)$html) === '')) {
            if (! is_plugin_active('testimonial')) {
                return $html;
            }

            $rawIds = data_get($compiler, 'testimonial_ids');
            $testimonialIds = [];
            if ($rawIds) {
                if (is_array($rawIds)) {
                    $testimonialIds = array_filter($rawIds);
                } else {
                    $testimonialIds = array_filter(explode(',', (string) $rawIds));
                }
            }

            $query = Testimonial::query()->wherePublished();

            if (! empty($testimonialIds)) {
                $query->whereIn('id', $testimonialIds);
            }

            $testimonials = $query->orderByRaw('CASE WHEN image IS NOT NULL AND image != "" THEN 0 ELSE 1 END, id ASC')
                ->take((int) (data_get($compiler, 'number_of_displays') ?: 10))
                ->get();

            if ($testimonials->isNotEmpty()) {
                return (string) Theme::partial('shortcodes.testimonials.index', [
                    'shortcode' => $compiler,
                    'testimonials' => $testimonials,
                ]);
            }
        }

        return $html;
    }, 20, 4);
});

// Suppress only the quote view while rendering card hooks; preserve every other hook.
app('view')->composer('plugins/fob-request-quote::button', function ($view): void {
    if (request()->attributes->get('farmart_child_card_context', false)) {
        $view->setPath(__DIR__ . '/../partials/ecommerce/card-quote-placeholder.blade.php');
    }
});
add_filter('farmart_child_product_card_extra_html', function ($html, $product): string {
    $previous = request()->attributes->get('farmart_child_card_context', false);
    request()->attributes->set('farmart_child_card_context', true);
    try {
        return (string) apply_filters(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, $html, $product);
    } finally {
        request()->attributes->set('farmart_child_card_context', $previous);
    }
}, 20, 2);

/**
 * Safe Theme Guidelines Implementation for Farmart Child Theme.
 * Prevents regex truncation, avoids breaking sibling HTML/scripts,
 * disables disruptive pseudo-overlays, and enables guidelines for AJAX UI blocks.
 */
if (! function_exists('farmart_child_insert_guideline_link')) {
    function farmart_child_insert_guideline_link(?string $link, ?string $html): ?string
    {
        if (! $link || ! $html || trim($html) === '') {
            return $html;
        }

        if (str_contains($html, 'guideline-edit-link')) {
            return $html;
        }

        // Identify script and style ranges so we NEVER insert inside them
        $skipRanges = [];
        if (preg_match_all('/<(script|style)\b[^>]*>.*?<\/\1>/is', $html, $scriptMatches, PREG_OFFSET_CAPTURE)) {
            foreach ($scriptMatches[0] as $sm) {
                $skipRanges[] = [$sm[1], $sm[1] + strlen($sm[0])];
            }
        }

        // Find the first valid opening HTML tag that is a container (not void or script/style)
        if (preg_match_all('/<([a-z1-6]+)([^>]*)>/i', $html, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            $skipTags = ['img', 'input', 'br', 'hr', 'meta', 'link', 'script', 'style', 'source', 'track', 'wbr'];
            foreach ($matches as $match) {
                $offset = $match[0][1];
                $isInsideScriptOrStyle = false;
                foreach ($skipRanges as $range) {
                    if ($offset >= $range[0] && $offset < $range[1]) {
                        $isInsideScriptOrStyle = true;
                        break;
                    }
                }
                if ($isInsideScriptOrStyle) {
                    continue;
                }

                $tag = strtolower($match[1][0]);
                if (! in_array($tag, $skipTags, true)) {
                    $insertPos = $offset + strlen($match[0][0]);
                    return substr($html, 0, $insertPos) . $link . substr($html, $insertPos);
                }
            }
        }

        return $link . $html;
    }
}

// 1. Remove broken core listeners
Filter::removeListener('widget_rendered.9999');
Filter::removeListener('shortcode_content_compiled.9999');

app('events')->listen('theme.beforeRenderTheme', function (): void {
    Filter::removeListener('shortcode_content_compiled.9999');
    Filter::removeListener('widget_rendered.9999');
});

app('events')->listen(RenderingTheme::class, function (): void {
    Filter::removeListener('shortcode_content_compiled.9999');
    Filter::removeListener('widget_rendered.9999');
});

add_filter('shortcode_content_compiled', function (?string $html): ?string {
    Filter::removeListener('shortcode_content_compiled.9999');
    return $html;
}, 1);

// 2. Add safe replacement for widget guidelines
add_filter('widget_rendered', function (?string $html, $widget): ?string {
    if (! setting('show_theme_guideline_link', false)
        || ! Auth::guard('web')->check()
        || ! Auth::guard('web')->user()?->hasPermission('widgets.index')
        || request()->input('visual_builder')
    ) {
        return $html;
    }

    if (is_object($widget) && method_exists($widget, 'getConfig') && $widget->getConfig('ignore_guideline')) {
        return $html;
    }

    $widgetId = is_object($widget) && method_exists($widget, 'getId') ? $widget->getId() : (is_string($widget) ? $widget : '');
    $editLink = route('widgets.index') . ($widgetId ? '?widget=' . urlencode($widgetId) : '');

    $link = view('packages/theme::guideline-link', [
        'html' => $html,
        'editLink' => $editLink,
        'editLabel' => trans('packages/theme::theme.widgets.edit_this_widget') ?: 'Edit this widget',
    ])->render();

    $link = preg_replace('/<a\b/i', '<a v-pre', $link, 1);

    return farmart_child_insert_guideline_link($link, $html);
}, 10000, 2);

// 3. Add safe replacement for shortcode guidelines (including AJAX UI blocks)
add_filter('shortcode_content_compiled', function (?string $html, string $name, $callback = null, $compiler = null): ?string {
    if (! setting('show_theme_guideline_link', false)
        || ! Auth::guard('web')->check()
        || request()->input('visual_builder')
    ) {
        return $html;
    }

    $user = Auth::guard('web')->user();
    if (! $user || (! $user->hasPermission('pages.edit') && ! $user->hasPermission('widgets.index'))) {
        return $html;
    }

    $editLink = null;
    if ($compiler && method_exists($compiler, 'getEditLink')) {
        $editLink = $compiler->getEditLink();
    }

    if (! $editLink) {
        $homepageId = BaseHelper::getHomepageId();
        if ($homepageId && Route::has('pages.edit')) {
            $editLink = route('pages.edit', $homepageId) . '?shortcode=' . urlencode($name);
        } elseif (Route::has('pages.index')) {
            $editLink = route('pages.index') . '?shortcode=' . urlencode($name);
        }
    } else {
        $editLink .= (str_contains($editLink, '?') ? '&' : '?') . 'shortcode=' . urlencode($name);
    }

    if (! $editLink) {
        return $html;
    }

    $link = view('packages/theme::guideline-link', [
        'html' => $html,
        'editLink' => $editLink,
        'editLabel' => trans('packages/theme::theme.shortcode_labels.edit_this_shortcode') ?: 'Edit this shortcode',
    ])->render();

    $link = preg_replace('/<a\b/i', '<a v-pre', $link, 1);

    return farmart_child_insert_guideline_link($link, $html);
}, 10000, 4);

// 4. Inject child theme CSS override in head for proper display and to disable broken overlay
add_filter(THEME_FRONT_HEADER, function (?string $html): ?string {
    if (! setting('show_theme_guideline_link', false)
        || ! Auth::guard('web')->check()
        || ! Auth::guard('web')->user()?->hasPermission('widgets.index')
        || request()->input('visual_builder')
    ) {
        return $html;
    }

    $css = '<style id="farmart-child-guideline-override">
        :has(> .guideline-edit-link) {
            position: relative !important;
        }
        .guideline-edit-link {
            opacity: 0.9 !important;
            position: absolute !important;
            top: 10px !important;
            right: 10px !important;
            z-index: 1045 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 28px !important;
            height: 28px !important;
            background-color: #0dcaf0 !important;
            border: 1px solid #0aa2c0 !important;
            border-radius: 50% !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25) !important;
            transition: all 0.2s ease-in-out !important;
            text-decoration: none !important;
        }
        .guideline-edit-link svg {
            width: 14px !important;
            height: 14px !important;
            color: #fff !important;
            stroke: currentColor !important;
        }
        .guideline-edit-link:hover {
            opacity: 1 !important;
            transform: scale(1.15) !important;
            background-color: #0bacbe !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.35) !important;
        }
        :has(> .guideline-edit-link):after,
        :has(> .guideline-edit-link):has(> .guideline-edit-link:hover):after {
            display: none !important;
            pointer-events: none !important;
        }
    </style>';

    return ($html ?? '') . $css;
}, 25);
