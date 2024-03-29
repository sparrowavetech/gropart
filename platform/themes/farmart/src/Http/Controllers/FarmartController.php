<?php

namespace Theme\Farmart\Http\Controllers;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Concerns\Http\Ajax\HasSearchProducts;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\Wishlist as WishlistModel;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Marketplace\Models\Store;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Http\Controllers\PublicController;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Theme\Farmart\Http\Requests\ContactSellerRequest;
use Theme\Farmart\Supports\Wishlist;

class FarmartController extends PublicController
{
    use HasSearchProducts;

    public function __construct(protected BaseHttpResponse $httpResponse)
    {
        $this->middleware(function ($request, $next) {
            if (! $request->ajax()) {
                return $this->httpResponse->setNextUrl(route('public.index'));
            }

            return $next($request);
        })->only([
            'ajaxCart',
            'ajaxGetQuickView',
            'ajaxAddProductToWishlist',
            'ajaxSearchProducts',
            'ajaxGetRecentlyViewedProducts',
            'ajaxContactSeller',
            'ajaxGetProductsByCollection',
            'ajaxGetProductsByCategory',
        ]);
    }

    public function ajaxCart()
    {
        return $this->httpResponse->setData([
            'count' => Cart::instance('cart')->count(),
            'total_price' => format_price(Cart::instance('cart')->rawSubTotal() + Cart::instance('cart')->rawTax()),
            'html' => Theme::partial('cart-mini.list'),
        ]);
    }

    public function ajaxAddProductToWishlist(Request $request, $productId = null)
    {
        if (! EcommerceHelper::isWishlistEnabled()) {
            abort(404);
        }

        if (! $productId) {
            $productId = $request->input('product_id');
        }

        if (! $productId) {
            return $this->httpResponse->setError()->setMessage(__('This product is not available.'));
        }

        $product = Product::query()->findOrFail($productId);

        $messageAdded = __('Added product :product successfully!', ['product' => $product->name]);
        $messageRemoved = __('Removed product :product from wishlist successfully!', ['product' => $product->name]);

        if (! auth('customer')->check()) {
            $duplicates = Cart::instance('wishlist')->search(function ($cartItem) use ($productId) {
                return $cartItem->id == $productId;
            });

            if (! $duplicates->isEmpty()) {
                $added = false;
                Cart::instance('wishlist')->search(function ($cartItem, $rowId) use ($productId) {
                    if ($cartItem->id == $productId) {
                        Cart::instance('wishlist')->remove($rowId);

                        return true;
                    }

                    return false;
                });
            } else {
                $added = true;
                Cart::instance('wishlist')
                    ->add($productId, $product->name, 1, $product->front_sale_price)
                    ->associate(Product::class);
            }

            return $this->httpResponse
                ->setMessage($added ? $messageAdded : $messageRemoved)
                ->setData([
                    'count' => Cart::instance('wishlist')->count(),
                    'added' => $added,
                ]);
        }

        $customer = auth('customer')->user();

        if (is_added_to_wishlist($productId)) {
            $added = false;
            WishlistModel::query()->where([
                'product_id' => $productId,
                'customer_id' => $customer->getKey(),
            ])->delete();
        } else {
            $added = true;
            WishlistModel::query()->create([
                'product_id' => $productId,
                'customer_id' => $customer->getKey(),
            ]);
        }

        return $this->httpResponse
            ->setMessage($added ? $messageAdded : $messageRemoved)
            ->setData([
                'count' => $customer->wishlist()->count(),
                'added' => $added,
            ]);
    }

    public function ajaxGetRecentlyViewedProducts(ProductInterface $productRepository)
    {
        if (! EcommerceHelper::isEnabledCustomerRecentlyViewedProducts()) {
            abort(404);
        }

        $queryParams = [
                'with' => ['slugable'],
                'take' => 12,
            ] + EcommerceHelper::withReviewsParams();

        if (auth('customer')->check()) {
            $products = $productRepository->getProductsRecentlyViewed(auth('customer')->id(), $queryParams);
        } else {
            $products = collect();

            $itemIds = collect(Cart::instance('recently_viewed')->content())
                ->sortBy([['updated_at', 'desc']])
                ->take(12)
                ->pluck('id')
                ->all();

            if ($itemIds) {
                $products = $productRepository->getProductsByIds($itemIds, $queryParams);
            }
        }

        return $this->httpResponse
            ->setData(Theme::partial('ecommerce.recently-viewed-products', compact('products')));
    }

    public function ajaxContactSeller(ContactSellerRequest $request, BaseHttpResponse $response)
    {
        $store = Store::query()->findOrFail($request->input('store_id'));

        EmailHandler::setModule(Theme::getThemeName())
            ->setVariableValues([
                'contact_message' => $request->input('content'),
                'customer_name' => $request->input('name'),
                'customer_email' => $request->input('email'),
                'store_name' => $store->name,
                'store_phone' => $store->phone,
                'store_address' => $store->full_address,
                'store_link' => $store->url,
                'store' => $store->toArray(),
            ])
            ->sendUsingTemplate('contact-seller', $store->email, [], false, 'themes');

        return $response->setMessage(__('Send message successfully!'));
    }

    public function ajaxGetProductsByCollection(int|string $id, Request $request, BaseHttpResponse $response)
    {
        if (! $request->expectsJson()) {
            return $response->setNextUrl(route('public.index'));
        }

        $products = get_products_by_collections(array_merge([
            'collections' => [
                'by' => 'id',
                'value_in' => [$id],
            ],
            'take' => $request->integer('limit') ?: 8,
            'with' => EcommerceHelper::withProductEagerLoadingRelations(),
        ], EcommerceHelper::withReviewsParams()));

        $wishlistIds = Wishlist::getWishlistIds($products->pluck('id')->all());

        $data = [];
        foreach ($products as $product) {
            $data[] = '<div class="product-inner">' . Theme::partial('ecommerce.product-item', compact('product', 'wishlistIds')) . '</div>';
        }

        return $response->setData($data);
    }

    public function ajaxGetProductsByCategory(
        int|string $id,
        Request $request,
        BaseHttpResponse $response,
        ProductInterface $productRepository
    ) {
        if (! $request->expectsJson()) {
            return $response->setNextUrl(route('public.index'));
        }

        $category = ProductCategory::query()
            ->where('id', $id)
            ->wherePublished()
            ->with([
                'activeChildren' => function (HasMany $query) {
                    return $query->limit(3);
                },
            ])
            ->first();

        if (! $category) {
            return $response->setData([]);
        }

        $products = $productRepository->getProductsByCategories(array_merge([
            'categories' => [
                'by' => 'id',
                'value_in' => array_merge([$category->id], $category->activeChildren->pluck('id')->all()),
            ],
            'take' => $request->integer('limit', 8) ?: 8,
        ], EcommerceHelper::withReviewsParams()));

        $wishlistIds = Wishlist::getWishlistIds($products->pluck('id')->all());

        $data = [];
        foreach ($products as $product) {
            $data[] = '<div class="product-inner">' . Theme::partial('ecommerce.product-item', compact('product', 'wishlistIds')) . '</div>';
        }

        return $response->setData($data);
    }
}
