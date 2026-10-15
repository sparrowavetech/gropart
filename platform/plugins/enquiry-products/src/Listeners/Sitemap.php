<?php
namespace SparroWave\EnquiryProducts\Listeners;
use Botble\Theme\Facades\SiteMapManager;
use Botble\Theme\Events\RenderingSiteMapEvent;
use SparroWave\EnquiryProducts\Support\Catalog;
class Sitemap
{
    public function handle(RenderingSiteMapEvent $event): void
    {
        $query = app(Catalog::class)->products()->whereHas('slugable');
        $size = SiteMapManager::getItemsPerPage();
        if (! $event->key) {
            $pages = max(1, (int) ceil((clone $query)->count() / $size));
            for ($page = 1; $page <= $pages; $page++) SiteMapManager::addSitemap(SiteMapManager::route('enquiry-products-' . $page), (clone $query)->max('updated_at'));
            return;
        }
        if (! preg_match('/^enquiry-products-(\d+)$/', $event->key, $matches)) return;
        $page = max(1, (int) $matches[1]);
        if ($page === 1) SiteMapManager::add(route('public.enquiry-products.index'), null, '0.8', 'weekly');
        $products = $query->with('slugable')->orderBy('id')->forPage($page, $size)->get();
        foreach ($products as $product) SiteMapManager::add(route('public.enquiry-products.show', $product->slugable->key), $product->updated_at, '0.8', 'weekly');
        if ($page === 1 && is_plugin_active('marketplace')) {
            $ids = app(Catalog::class)->products()->whereNotNull('store_id')->select('store_id')->distinct();
            foreach (\Botble\Marketplace\Models\Store::wherePublished()->whereIn('id', $ids)->with('slugable')->get() as $store) {
                if ($store->slugable?->key) SiteMapManager::add(route('public.enquiry-products.store', $store->slugable->key), $store->updated_at, '0.7', 'weekly');
            }
        }
    }
}
