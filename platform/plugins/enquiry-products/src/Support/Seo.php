<?php
namespace SparroWave\EnquiryProducts\Support;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Media\Facades\RvMedia;
use Illuminate\Support\Str;
class Seo
{
    public static function page(string $title, string $description, string $url, array $schema = [], bool $noindex = false): void
    {
        SeoHelper::setTitle($title)->setDescription(Str::limit(trim(html_entity_decode(strip_tags($description))), 160, '…'));
        SeoHelper::meta()->setUrl($url);
        SeoHelper::openGraph()->setUrl($url);
        add_filter('core_seo_canonical', fn () => $url, 999);
        add_filter(THEME_FRONT_HEADER, function (?string $html) use ($schema, $noindex) {
            if ($noindex) $html .= '<meta name="robots" content="noindex,follow">';
            if ($schema) $html .= '<script type="application/ld+json">' . json_encode($schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
            return $html;
        }, 200);
    }
    public static function product($product): void
    {
        $url = route('public.enquiry-products.show', $product->slugable->key);
        $image = RvMedia::getImageUrl($product->image, null, false, RvMedia::getDefaultImage());
        $description = trim(html_entity_decode(strip_tags($product->description))) ?: 'Request information about ' . $product->name . '.';
        $schema = ['@context'=>'https://schema.org','@type'=>'Product','@id'=>$url.'#product','url'=>$url,'name'=>$product->name,'description'=>$description,'image'=>[$image]];
        if ($product->sku) $schema['sku'] = $product->sku;
        if ($product->brand?->name) $schema['brand'] = ['@type'=>'Brand','name'=>$product->brand->name];
        self::page($product->name, $description, $url, ['@context'=>'https://schema.org','@graph'=>[
            $schema,
            ['@type'=>'BreadcrumbList','itemListElement'=>[
                ['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>url('/')],
                ['@type'=>'ListItem','position'=>2,'name'=>setting('ep_catalog_title', "Equipment's Enquiry"),'item'=>route('public.enquiry-products.index')],
                ['@type'=>'ListItem','position'=>3,'name'=>$product->name,'item'=>$url],
            ]],
        ]]);
        SeoHelper::setImage($image);
    }
}
