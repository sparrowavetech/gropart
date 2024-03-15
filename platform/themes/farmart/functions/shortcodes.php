<?php

use Botble\Ads\Facades\AdsManager;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\FlashSale as FlashSaleFacade;
use Botble\Ecommerce\Facades\ProductCategoryHelper;
use Botble\Ecommerce\Models\FlashSale;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductCollection;
use Botble\Testimonial\Models\Testimonial;
use Botble\Testimonial\Repositories\Interfaces\TestimonialInterface;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Faq\Models\FaqCategory;
use Botble\Media\Facades\RvMedia;
use Botble\Shortcode\Compilers\Shortcode;
use Botble\Shortcode\Facades\Shortcode as ShortcodeFacade;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Supports\ThemeSupport;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Theme\Farmart\Supports\Wishlist;

app()->booted(function () {
    ThemeSupport::registerGoogleMapsShortcode();
    ThemeSupport::registerYoutubeShortcode();

    function image_placeholder(?string $default = null, ?string $size = null): string
    {
        if (theme_option('lazy_load_image_enabled', 'yes') != 'yes' && $default) {
            if (Str::contains($default, ['https://', 'http://'])) {
                return $default;
            }

            return RvMedia::getImageUrl($default, $size);
        }

        if ($placeholder = theme_option('image-placeholder')) {
            return RvMedia::getImageUrl($placeholder);
        }

        return Theme::asset()->url('images/placeholder.png');
    }

    if (is_plugin_active('simple-slider')) {
        add_filter(SIMPLE_SLIDER_VIEW_TEMPLATE, function () {
            return Theme::getThemeNamespace() . '::partials.shortcodes.sliders';
        }, 120);

        add_filter(SHORTCODE_REGISTER_CONTENT_IN_ADMIN, function (string|null $data, string $key, array $attributes) {
            if ($key == 'simple-slider' && is_plugin_active('ads')) {
                $ads = AdsManager::getData(true, true);

                $defaultAutoplay = 'yes';

                return $data . Theme::partial('shortcodes.includes.autoplay-settings', compact('attributes', 'defaultAutoplay')) .
                    Theme::partial('shortcodes.select-ads-admin-config', compact('ads', 'attributes'));
            }

            return $data;
        }, 50, 3);
    }

    if (is_plugin_active('ads')) {
        function display_ads_advanced(?string $key, array $attributes = []): ?string
        {
            return AdsManager::displayAds($key, $attributes);
        }

        add_shortcode('theme-ads', __('Theme ads'), __('Theme ads'), function (Shortcode $shortcode) {
            $ads = [];
            $attributes = $shortcode->toArray();

            for ($i = 1; $i < 5; $i++) {
                if (isset($attributes['key_' . $i]) && ! empty($attributes['key_' . $i])) {
                    $ad = display_ads_advanced((string)$attributes['key_' . $i]);
                    if ($ad) {
                        $ads[] = $ad;
                    }
                }
            }

            $ads = array_filter($ads);

            if (! count($ads)) {
                return null;
            }

            return Theme::partial('shortcodes.ads.theme-ads', compact('ads'));
        });

        shortcode()->setAdminConfig('theme-ads', function (array $attributes) {
            $ads = AdsManager::getData(true, true);

            return Theme::partial('shortcodes.ads.theme-ads-admin-config', compact('ads', 'attributes'));
        });
    }

    if (is_plugin_active('ecommerce')) {
        add_shortcode(
            'featured-product-categories',
            __('Featured Product Categories'),
            __('Featured Product Categories'),
            function (Shortcode $shortcode) {
                $categories = ProductCategoryHelper::getProductCategoriesWithUrl([], ['is_featured' => true], $shortcode->limit);

                return Theme::partial('shortcodes.ecommerce.featured-product-categories', compact('shortcode', 'categories'));
            }
        );

        shortcode()->setAdminConfig('featured-product-categories', function (array $attributes) {
            return Theme::partial('shortcodes.ecommerce.featured-product-categories-admin-config', compact('attributes'));
        });

        add_shortcode('featured-brands', __('Featured Brands'), __('Featured Brands'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.ecommerce.featured-brands', compact('shortcode'));
        });

        shortcode()->setAdminConfig('featured-brands', function (array $attributes) {
            return Theme::partial('shortcodes.ecommerce.featured-brands-admin-config', compact('attributes'));
        });

        add_shortcode('all-brands', __('All Brands'), __('All Brands'), function(Shortcode $shortcode){
            return Theme::partial('shortcodes.ecommerce.all-brands', compact('shortcode'));
        });
        shortcode()->setAdminConfig('all-brands', function (array $attributes) {
            return Theme::partial('shortcodes.ecommerce.all-brands-admin-config', compact('attributes'));
        });

        if (FlashSaleFacade::isEnabled()) {
            add_shortcode('flash-sale', __('Flash sale'), __('Flash sale'), function (Shortcode $shortcode) {
                $flashSale = FlashSale::query()
                    ->notExpired()
                    ->where('id', $shortcode->flash_sale_id)
                    ->wherePublished()
                    ->with([
                        'products' => function ($query) {
                            $reviewParams = EcommerceHelper::withReviewsParams();

                            if (EcommerceHelper::isReviewEnabled()) {
                                $query->withAvg($reviewParams['withAvg'][0], $reviewParams['withAvg'][1]);
                            }

                            return $query
                                ->wherePublished()
                                ->with(EcommerceHelper::withProductEagerLoadingRelations())
                                ->withCount($reviewParams['withCount']);
                        },
                    ])
                    ->first();

                if (! $flashSale || $flashSale->products->isEmpty()) {
                    return null;
                }

                $isFlashSale = true;
                $wishlistIds = Wishlist::getWishlistIds($flashSale->products->pluck('id')->all());

                return Theme::partial('shortcodes.ecommerce.flash-sale', compact('shortcode', 'flashSale', 'isFlashSale', 'wishlistIds'));
            });

            shortcode()->setAdminConfig('flash-sale', function (array $attributes) {
                $flashSales = FlashSale::query()
                    ->wherePublished()
                    ->notExpired()
                    ->get();

                return Theme::partial('shortcodes.ecommerce.flash-sale-admin-config', compact('flashSales', 'attributes'));
            });
        }

        add_shortcode(
            'product-collections',
            __('Product Collections'),
            __('Product Collections'),
            function (Shortcode $shortcode) {
                if ($shortcode->collection_id) {
                    $collectionIds = [$shortcode->collection_id];
                } else {
                    $collectionIds = ProductCollection::query()
                        ->wherePublished()
                        ->pluck('id')
                        ->all();
                }

                $limit = (int)$shortcode->limit ?: 8;

                $products = get_products_by_collections(array_merge([
                    'collections' => [
                        'by' => 'id',
                        'value_in' => $collectionIds,
                    ],
                    'take' => $limit,
                    'with' => EcommerceHelper::withProductEagerLoadingRelations(),
                ], EcommerceHelper::withReviewsParams()));

                if ($products->isEmpty()) {
                    return null;
                }

                $wishlistIds = Wishlist::getWishlistIds($products->pluck('id')->all());

                return Theme::partial('shortcodes.ecommerce.product-collections', [
                    'title' => $shortcode->title,
                    'limit' => $limit,
                    'shortcode' => $shortcode,
                    'products' => $products,
                    'wishlistIds' => $wishlistIds,
                ]);
            }
        );

        shortcode()->setAdminConfig('product-collections', function (array $attributes) {
            $productCollections = get_product_collections(select: ['id', 'name', 'slug']);

            return Theme::partial('shortcodes.ecommerce.product-collections-admin-config', compact('attributes', 'productCollections'));
        });

        add_shortcode(
            'product-category-products',
            __('Product category products'),
            __('Product category products'),
            function (Shortcode $shortcode) {
                $category = ProductCategory::query()
                    ->wherePublished()
                    ->where('id', (int)$shortcode->category_id)
                    ->with([
                        'activeChildren' => function (HasMany $query) {
                            return $query->limit(3);
                        },
                    ])
                    ->first();

                if (! $category) {
                    return null;
                }

                $limit = (int)$shortcode->limit ?: 8;

                $products = app(ProductInterface::class)->getProductsByCategories(array_merge([
                    'categories' => [
                        'by' => 'id',
                        'value_in' => array_merge([$category->id], $category->activeChildren->pluck('id')->all()),
                    ],
                    'take' => $limit,
                ], EcommerceHelper::withReviewsParams()));

                if ($products->isEmpty()) {
                    return null;
                }

                $wishlistIds = Wishlist::getWishlistIds($products->pluck('id')->all());

                return Theme::partial('shortcodes.ecommerce.product-category-products', compact('category', 'products', 'shortcode', 'limit', 'wishlistIds'));
            }
        );

        shortcode()->setAdminConfig('product-category-products', function (array $attributes) {
            return Theme::partial('shortcodes.ecommerce.product-category-products-admin-config', compact('attributes'));
        });

        add_shortcode('featured-products', __('Featured products'), __('Featured products'), function (Shortcode $shortcode) {
            $request = request();

            $products = get_featured_products([
                    'take' => $request->integer('limit', 10),
                    'with' => EcommerceHelper::withProductEagerLoadingRelations(),
                ] + EcommerceHelper::withReviewsParams());

            if ($products->isEmpty()) {
                return null;
            }

            $wishlistIds = Wishlist::getWishlistIds(collect($products->toArray())->pluck('id')->all());

            return Theme::partial('shortcodes.ecommerce.featured-products', [
                'shortcode' => $shortcode,
                'products' => $products,
                'wishlistIds' => $wishlistIds,
            ]);
        });

        shortcode()->setAdminConfig('featured-products', function (array $attributes) {
            return Theme::partial('shortcodes.ecommerce.featured-products-admin-config', compact('attributes'));
        });
    }

    if (is_plugin_active('blog')) {
        add_shortcode('featured-posts', __('Featured Blog Posts'), __('Featured Blog Posts'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.featured-posts', compact('shortcode'));
        });

        shortcode()->setAdminConfig('featured-posts', function (array $attributes) {
            return Theme::partial('shortcodes.featured-posts-admin-config', compact('attributes'));
        });

        add_shortcode('blog-posts-by-category', __('Blog Posts by Category'), __('Blog Posts by Category'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.blog-posts-by-category', compact('shortcode'));
        });

        shortcode()->setAdminConfig('blog-posts-by-category', function (array $attributes) {
            return Theme::partial('shortcodes.blog-posts-by-category-admin-config', compact('attributes'));
        });
    }

    if (is_plugin_active('contact')) {
        add_filter(CONTACT_FORM_TEMPLATE_VIEW, function () {
            return Theme::getThemeNamespace() . '::partials.shortcodes.contact-form';
        }, 120);
    }

    add_shortcode('contact-info-boxes', __('Contact info boxes'), __('Contact info boxes'), function (Shortcode $shortcode) {
        return Theme::partial('shortcodes.contact-info-boxes', compact('shortcode'));
    });

    shortcode()->setAdminConfig('contact-info-boxes', function (array $attributes) {
        return Theme::partial('shortcodes.contact-info-boxes-admin-config', compact('attributes'));
    });

    if (is_plugin_active('faq')) {
        add_shortcode('faq', __('FAQs'), __('FAQs'), function (Shortcode $shortcode) {
            $categories = FaqCategory::query()
                ->wherePublished()
                ->with([
                    'faqs' => function (HasMany $query) {
                        $query->wherePublished();
                    },
                ])
                ->orderBy('order')
                ->orderByDesc('created_at')
                ->get();

            return Theme::partial('shortcodes.faq', [
                'title' => $shortcode->title,
                'categories' => $categories,
            ]);
        });

        shortcode()->setAdminConfig('faq', function (array $attributes) {
            return Theme::partial('shortcodes.faq-admin-config', compact('attributes'));
        });

        add_shortcode('faq-by-category', __('FAQs by Category'), __('FAQs by Category'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.faq-by-category', compact('shortcode'));
        });

        shortcode()->setAdminConfig('faq-by-category', function (array $attributes) {
            return Theme::partial('shortcodes.faq-by-category-admin-config', compact('attributes'));
        });

        add_shortcode('faq-by-group', __('Group FAQs'), __('Group FAQs'), function (Shortcode $shortcode) {
            return Theme::partial('shortcodes.faq-by-group', compact('shortcode'));
        });

        shortcode()->setAdminConfig('faq-by-group', function (array $attributes) {
            return Theme::partial('shortcodes.faq-by-group-admin-config', compact('attributes'));
        });
    }

    if (is_plugin_active('testimonial')) {
        add_shortcode('testimonials', __('Testimonials'), __('Testimonials'), function (Shortcode $shortcode) {
            $testimonials = app(TestimonialInterface::class)->advancedGet([
                'condition' => [
                    'status' => BaseStatusEnum::PUBLISHED,
                ],
                'take' => (int)$shortcode->number_of_displays,
            ]);

            return Theme::partial('shortcodes.testimonials.index', compact('shortcode', 'testimonials'));
        });

        shortcode()->setAdminConfig('testimonials', function (array $attributes) {
            return Theme::partial('shortcodes.testimonials.admin-config', compact('attributes'));
        });
    }

    add_shortcode('site-features', __('Site features'), __('Site features'), function (Shortcode $shortcode) {
        return Theme::partial('shortcodes.site-features', compact('shortcode'));
    });

    ShortcodeFacade::setAdminConfig('site-features', function (array $attributes) {
        $fields = [
            'title' => [
                'title' => __('Title'),
                'required' => true,
            ],
            'subtitle' => [
                'title' => __('Subtitle'),
                'required' => true,
            ],
            'icon' => [
                'type' => 'image',
                'title' => __('Icon'),
                'required' => true,
            ],
        ];

        return ShortcodeForm::createFromArray($attributes)
            ->add('title', 'text', [
                'label' => __('Title'),
            ])
            ->add('feature_tabs', 'tabs', [
                'fields' => $fields,
                'shortcode_attributes' => $attributes,
            ]);
    });

    add_shortcode('newsletter-home', __('Newsletter Form'), __('Newsletter Form'), function (Shortcode $shortcode) {
        return Theme::partial('shortcodes.newsletter-home', compact('shortcode'));
    });

    shortcode()->setAdminConfig('newsletter-home', function (array $attributes) {
        return Theme::partial('shortcodes.newsletter-home-admin-config', compact('attributes'));
    });

    add_shortcode('coming-soon', __('Coming Soon'), __('Coming Soon'), function (Shortcode $shortcode) {
        return Theme::partial('shortcodes.coming-soon', compact('shortcode'));
    });

    shortcode()->setAdminConfig('coming-soon', function (array $attributes) {
        return Theme::partial('shortcodes.coming-soon-admin-config', compact('attributes'));
    });
});
