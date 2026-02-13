<?php

namespace FriendsOfBotble\FacebookCatalogFeed\Services;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\Product;
use Botble\Media\Facades\RvMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;

class FacebookCatalogFeedService
{
    protected $output;
    protected $includeVariations;
    protected $includeOutOfStock;

    public function streamFeed(string $type = 'all')
    {
        // Disable time limit
        set_time_limit(0);

        // Initialize settings
        $this->includeVariations = setting('fob_facebook_catalog_feed_include_variations', false);
        $this->includeOutOfStock = setting('fob_facebook_catalog_feed_include_out_of_stock', false);

        return Response::stream(function () use ($type) {
            $this->output = fopen('php://output', 'w');

            // Write XML header
            $this->write('<?xml version="1.0" encoding="UTF-8"?>');
            $this->write('<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">');
            $this->write('<channel>');
            $this->write('<title>' . $this->escape(theme_option('site_title', 'Shop')) . '</title>');
            $this->write('<link>' . url('/') . '</link>');
            $this->write('<description>' . $this->escape(theme_option('seo_description', 'Product catalog')) . '</description>');

            // Process products
            $this->processProducts($type);

            // Close XML
            $this->write('</channel>');
            $this->write('</rss>');

            fclose($this->output);
        }, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    protected function write(string $content): void
    {
        fwrite($this->output, $content . "\n");
        flush();
    }

    protected function escape($value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    protected function processProducts(string $type): void
    {
        $query = $this->getProductQuery($type);

        // Use cursor for memory efficiency
        foreach ($query->cursor() as $product) {
            $this->addProductToFeed($product);
        }
    }

    protected function getProductQuery(string $type): Builder
    {
        $query = Product::query()
            ->select([
                'id',
                'name',
                'description',
                'image',
                'images',
                'price',
                'sale_price',
                'sku',
                'barcode',
                'weight',
                'status',
                'is_variation',
                'with_storehouse_management',
                'quantity',
                'stock_status',
                'brand_id',
                'is_featured',
            ])
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->where('is_variation', false);

        if (! $this->includeOutOfStock) {
            $query->where(function ($q) {
                $q->where('with_storehouse_management', false)
                    ->where('stock_status', '!=', 'out_of_stock')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('with_storehouse_management', true)
                            ->where('quantity', '>', 0);
                    });
            });
        }

        switch ($type) {
            case 'new':
                $query->orderBy('created_at', 'desc')->limit(100);

                break;
            case 'featured':
                $query->where('is_featured', true);

                break;
            case 'on_sale':
                $query->whereNotNull('sale_price')
                    ->where('sale_price', '>', 0)
                    ->whereColumn('sale_price', '<', 'price');

                break;
        }

        // Only load relationships we need
        $query->with([
            'categories:id,name,parent_id',
            'brand:id,name',
        ]);

        if ($this->includeVariations) {
            $query->with(['variations' => function ($q) {
                $q->select('id', 'product_id', 'product_attributes', 'sku', 'price', 'sale_price', 'quantity', 'image', 'images', 'is_default');
            }]);
        }

        return $query;
    }

    protected function addProductToFeed(Product $product): void
    {
        if ($this->includeVariations && $product->variations && $product->variations->count() > 0) {
            foreach ($product->variations as $variation) {
                $this->writeProductItem($product, $variation);
            }
        } else {
            $this->writeProductItem($product);
        }
    }

    protected function writeProductItem(Product $product, $variation = null): void
    {
        $this->write('<item>');

        // Basic fields
        $id = $variation ? $product->id . '-' . $variation->id : $product->id;
        $title = $variation ? $product->name . ' - ' . $this->getVariationTitle($variation) : $product->name;

        $this->write('<g:id>' . $id . '</g:id>');
        $this->write('<g:title>' . $this->escape($title) . '</g:title>');
        $this->write('<g:description>' . $this->escape(strip_tags($product->description ?: $product->name)) . '</g:description>');

        // Generate product URL
        $this->write('<g:link>' . $this->escape($product->url) . '</g:link>');

        // Image
        $image = $variation->image ?? $product->image;
        if ($image) {
            $imageUrl = RvMedia::getImageUrl($image, null, false, RvMedia::getDefaultImage());
            $this->write('<g:image_link>' . $this->escape($imageUrl) . '</g:image_link>');
        }

        // Price
        $productData = $variation ?: $product;
        $price = $productData->price ?: 0;
        $salePrice = $productData->sale_price;

        $currency = get_application_currency()->title;
        $this->write('<g:price>' . number_format($price, 2, '.', '') . ' ' . $currency . '</g:price>');

        if ($salePrice && $salePrice < $price) {
            $this->write('<g:sale_price>' . number_format($salePrice, 2, '.', '') . ' ' . $currency . '</g:sale_price>');
        }

        // Availability
        $availability = $this->getAvailability($productData);
        $this->write('<g:availability>' . $availability . '</g:availability>');

        // Brand
        if ($product->brand) {
            $this->write('<g:brand>' . $this->escape($product->brand->name) . '</g:brand>');
        } elseif ($brandAttribute = setting('fob_facebook_catalog_feed_brand_attribute')) {
            $this->write('<g:brand>' . $this->escape($brandAttribute) . '</g:brand>');
        }

        // Condition
        $this->write('<g:condition>' . setting('fob_facebook_catalog_feed_condition', 'new') . '</g:condition>');

        // Identifiers
        $sku = $variation->sku ?? $product->sku;
        if ($sku) {
            $this->write('<g:mpn>' . $this->escape($sku) . '</g:mpn>');
        }

        if ($product->barcode) {
            $this->write('<g:gtin>' . $this->escape($product->barcode) . '</g:gtin>');
        }

        // Categories - simplified
        if ($product->categories && $product->categories->count() > 0) {
            $category = $product->categories->first();
            $this->write('<g:product_type>' . $this->escape($category->name) . '</g:product_type>');
        }

        $this->write('</item>');
    }

    protected function getVariationTitle($variation): string
    {
        if (! $variation->product_attributes) {
            return '';
        }

        $attributes = is_array($variation->product_attributes)
            ? $variation->product_attributes
            : json_decode($variation->product_attributes, true);

        if (! is_array($attributes)) {
            return '';
        }

        return implode(' - ', array_values($attributes));
    }

    protected function getAvailability($product): string
    {
        if ($product->with_storehouse_management && $product->quantity <= 0) {
            return setting('fob_facebook_catalog_feed_availability_out_of_stock', 'out of stock');
        }

        if (! $product->with_storehouse_management && $product->stock_status == 'out_of_stock') {
            return setting('fob_facebook_catalog_feed_availability_out_of_stock', 'out of stock');
        }

        return setting('fob_facebook_catalog_feed_availability_in_stock', 'in stock');
    }
}
