<?php

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
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
