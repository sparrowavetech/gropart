<?php
namespace SparroWave\EnquiryProducts\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use SparroWave\EnquiryProducts\Models\Enquiry;
use SparroWave\EnquiryProducts\Support\Catalog;
use SparroWave\EnquiryProducts\Support\Notifications;

class CatalogController extends BaseController
{
    public function index(Request $request, Catalog $catalog, ?string $storeSlug = null)
    {
        $input = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'category' => ['nullable', 'integer', 'min:1'], 'sort' => ['nullable', Rule::in(['default', 'name_asc', 'name_desc', 'newest'])]]);
        $search = $input['q'] ?? '';
        $category = (int) ($input['category'] ?? 0);
        $categories = $catalog->categories();
        abort_if($category && ! $categories->contains('id', $category), 404);
        $sort = $input['sort'] ?? 'default';
        $store = $this->catalogStore($storeSlug);
        $catalogUrl = $store ? route('public.enquiry-products.store', $storeSlug) : route('public.enquiry-products.index');
        $suggestionsUrl = $store ? route('public.enquiry-products.store-suggestions', $storeSlug) : route('public.enquiry-products.suggestions');
        $query = $catalog->listing($search, $category);
        if ($store) { $query->where('store_id', $store->id); }
        if (is_plugin_active('marketplace')) { $query->with('store'); }
        if ($sort === 'name_asc') { $query->reorder('ec_products.name'); }
        if ($sort === 'name_desc') { $query->reorder('ec_products.name', 'desc'); }
        if ($sort === 'newest') { $query->reorder('ec_products.created_at', 'desc'); }
        $products = $query->paginate(12)->withQueryString();
        Theme::set('withTitle', false);
        $title = setting('ep_catalog_title', "Equipment's Enquiry") . ($store ? ' — ' . $store->name : '');
        $canonical = $catalogUrl . ($products->currentPage() > 1 ? '?page=' . $products->currentPage() : '');
        \SparroWave\EnquiryProducts\Support\Seo::page($title, setting('ep_catalog_subtitle') ?: 'Browse equipment and send product enquiries directly to suppliers.', $canonical, [
            '@context'=>'https://schema.org', '@type'=>'CollectionPage', 'name'=>$title, 'url'=>$canonical,
            'mainEntity'=>['@type'=>'ItemList', 'itemListElement'=>$products->getCollection()->filter(fn ($item) => $item->slugable?->key)->values()->map(fn ($item, $index) => [
                '@type'=>'ListItem', 'position'=>($products->firstItem() ?? 1) + $index, 'url'=>route('public.enquiry-products.show', $item->slugable->key), 'name'=>$item->name,
            ])->all()],
        ], $search !== '' || $category > 0 || $sort !== 'default');
        return Theme::of('plugins/enquiry-products::catalog', compact('products', 'categories', 'search', 'category', 'sort', 'store', 'catalogUrl', 'suggestionsUrl'))->render();
    }

    public function suggestions(Request $request, Catalog $catalog, ?string $storeSlug = null)
    {
        $input = $request->validate(['q' => ['required', 'string', 'min:2', 'max:120'], 'category' => ['nullable', 'integer', 'min:1']]);
        $store = $this->catalogStore($storeSlug);
        $query = $catalog->listing($input['q'], isset($input['category']) ? (int) $input['category'] : null);
        if (is_plugin_active('marketplace')) {
            $query->with('store');
        }
        if ($store) { $query->where('store_id', $store->id); }
        return response()->json($query->whereHas('slugable')->limit(8)->get()->map(function ($product): array {
            $store = is_plugin_active('marketplace') ? $product->store : null;
            $type = $store && is_plugin_active('vendor-verified-badge')
                ? \SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper::getShopType($store)?->label() : null;
            return [
                'name' => $product->name,
                'url' => route('public.enquiry-products.show', $product->slugable->key),
                'image' => \Botble\Media\Facades\RvMedia::getImageUrl($product->image, 'small', false, \Botble\Media\Facades\RvMedia::getDefaultImage()),
                'seller' => $store?->name,
                'badge' => $type,
                'verified' => (bool) ($store?->is_verified ?? false),
            ];
        }));
    }

    private function catalogStore(?string $slug)
    {
        if ($slug === null) { return null; }
        abort_unless(is_plugin_active('marketplace'), 404);
        return \Botble\Marketplace\Models\Store::wherePublished()->whereHas('slugable', fn ($query) => $query->where('key', $slug))->firstOrFail();
    }

    public function legacyShow(int $id, Catalog $catalog)
    {
        $product = $catalog->products()->with('slugable')->findOrFail($id);
        abort_unless($product->slugable?->key, 404);

        return redirect()->route('public.enquiry-products.show', $product->slugable->key, 301);
    }

    public function show(string $slug, Catalog $catalog)
    {
        $product = $catalog->products()->with(['slugable', 'brand', 'tags'])->whereHas('slugable', fn ($query) => $query->where('key', $slug))->firstOrFail();
        if (is_plugin_active('marketplace')) { $product->load('store'); }
        Theme::set('withTitle', false);
        \SparroWave\EnquiryProducts\Support\Seo::product($product);
        Theme::breadcrumb()->add(setting('ep_catalog_title', "Equipment's Enquiry"), route('public.enquiry-products.index'))->add($product->name);
        return Theme::of('plugins/enquiry-products::product', compact('product'))->render();
    }

    public function enquiryForm(string $slug, Catalog $catalog)
    {
        $product = $catalog->products()->whereHas('slugable', fn ($query) => $query->where('key', $slug))->firstOrFail();
        $id = $product->id;
        $token = bin2hex(random_bytes(32));
        $tokens = array_slice(session('ep_submission', []), -19, null, true);
        $tokens[$token] = $id;
        session()->put('ep_submission', $tokens);
        \SparroWave\EnquiryProducts\Support\Seo::page($product->name . ' — ' . __('plugins/enquiry-products::messages.enquiry'), 'Send your equipment requirements.', route('public.enquiry-products.show', $slug), [], true);
        $countries = is_plugin_active('location') ? \Botble\Location\Models\Country::query()->orderBy('name')->get() : collect();
        Theme::set('withTitle', false);
        Theme::breadcrumb()->add(setting('ep_catalog_title', "Equipment's Enquiry"), route('public.enquiry-products.index'))
            ->add($product->name, route('public.enquiry-products.show', $slug))->add(__('plugins/enquiry-products::messages.enquiry'));
        return Theme::of('plugins/enquiry-products::enquiry-form', compact('product', 'token', 'countries'))->render();
    }

    public function submit(int $id, Request $request, Catalog $catalog)
    {
        $product = $catalog->products()->findOrFail($id);
        $request->merge(['phone' => preg_replace('/[\s()\-]/', '', (string) $request->input('phone'))]);
        if (is_plugin_active('location')) {
            $availableCountries = \Botble\Location\Models\Country::query()->pluck('id');
            if ($availableCountries->count() === 1) { $request->merge(['country' => $availableCountries->first()]); }
        }
        $captchaRules = is_plugin_active('captcha') ? \Botble\Captcha\Facades\Captcha::rules() : [];
        $data = $request->validate([
            ...$captchaRules,
            'token' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:80'],
            'phone' => ['required', 'string', 'regex:/^\+?[1-9][0-9]{7,14}$/'],
            'country' => ['nullable', 'integer', 'min:0'],
            'state' => ['nullable', 'integer', 'min:0'],
            'city' => ['nullable', 'integer', 'min:0'],
            'address' => ['required', 'string', 'max:1000'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'website' => ['nullable', 'max:0'],
        ]);
        abort_unless((int) session('ep_submission.' . $data['token']) === $id, 419);
        if ($existing = Enquiry::where('submission_token', $data['token'])->first()) {
            return redirect()->route('public.enquiry-products.success', $existing->submission_token);
        }
        if (is_plugin_active('location')) {
            $request->validate([
                'country' => ['nullable', Rule::exists('countries', 'id')],
                'state' => ['nullable', Rule::exists('states', 'id')->where('country_id', $data['country'] ?? 0)],
                'city' => ['nullable', Rule::exists('cities', 'id')->where('state_id', $data['state'] ?? 0)],
            ]);
        }
        $path = $request->file('attachment')?->store('enquiry-products', 'local');
        try {
            $enquiry = DB::transaction(function () use ($data, $product, $path): Enquiry {
                return Enquiry::create([
                    'product_id' => $product->id, 'store_id' => is_plugin_active('marketplace') ? ($product->store_id ?: null) : null,
                    'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'],
                    'country' => $data['country'] ?? 0, 'state' => $data['state'] ?? 0, 'city' => $data['city'] ?? 0,
                    'address' => $data['address'], 'zip_code' => $data['zip_code'] ?? null,
                    'quantity' => 1, 'description' => $data['description'] ?? null,
                    'attachment' => $path, 'submission_token' => $data['token'],
                    'status' => 'pending',
                ]);
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $exception) {
            if ($path) { Storage::disk('local')->delete($path); }
            $enquiry = Enquiry::where('submission_token', $data['token'])->first();
            if (! $enquiry) { throw $exception; }
            return redirect()->route('public.enquiry-products.success', $enquiry->submission_token);
        } catch (\Throwable $exception) {
            if ($path) { Storage::disk('local')->delete($path); }
            throw $exception;
        }
        app(Notifications::class)->send($enquiry);
        return redirect()->route('public.enquiry-products.success', $enquiry->submission_token);
    }

    public function success(string $token)
    {
        $enquiry = Enquiry::where('submission_token', $token)->firstOrFail();
        abort_unless((int) session('ep_submission.' . $token) === (int) $enquiry->product_id, 404);
        $enquiry->load('product.slugable');
        $product = $enquiry->product;
        $seller = is_plugin_active('marketplace') && $enquiry->store_id
            ? \Botble\Marketplace\Models\Store::find($enquiry->store_id) : null;
        SeoHelper::setTitle(__('plugins/enquiry-products::messages.enquiry_submitted_successfully'));
        return response()->view('plugins/enquiry-products::success', compact('enquiry', 'product', 'seller'))
            ->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
