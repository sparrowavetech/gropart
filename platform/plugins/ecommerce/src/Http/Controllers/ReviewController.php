<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Requests\SelectSearchAjaxRequest;
use Botble\Ecommerce\Forms\ReviewForm;
use Botble\Ecommerce\Http\Requests\ReviewRequest;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Review;
use Botble\Ecommerce\Tables\ReviewTable;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this
            ->breadcrumb()
            ->add(trans('plugins/ecommerce::review.name'), route('reviews.index'));
    }

    public function index(ReviewTable $dataTable)
    {
        $this->pageTitle(trans('plugins/ecommerce::review.name'));

        Assets::addStylesDirectly('vendor/core/plugins/ecommerce/css/review.css');

        return $dataTable->renderTable();
    }

    public function create()
    {
        return ReviewForm::create()->renderForm();
    }

    public function store(ReviewRequest $request)
    {
        $review = Review::query()
            ->where('customer_id', $request->input('customer_id'))
            ->where('product_id', $request->input('product_id'))
            ->exists();

        if ($review) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce::review.review_already_exists'));
        }

        $review = Review::query()->create($request->validated());

        event(new CreatedContentEvent('review', $request, $review));

        return $this
            ->httpResponse()
            ->setNextRoute('reviews.show', $review)
            ->withCreatedSuccessMessage();
    }

    public function show(int|string $id): View
    {
        Assets::addScriptsDirectly('vendor/core/plugins/ecommerce/js/admin-review.js')
            ->addStylesDirectly('vendor/core/plugins/ecommerce/css/review.css');

        $review = Review::query()
            ->with(['user', 'product' => function (BelongsTo $query) {
                $query
                    ->withCount('reviews')
                    ->withAvg('reviews', 'star');
            }])
            ->findOrFail($id);

        $this->pageTitle(trans('plugins/ecommerce::review.view', ['name' => $review->user->name]));

        return view('plugins/ecommerce::reviews.show', compact('review'));
    }

    public function destroy(int|string $id, Request $request)
    {
        try {
            $review = Review::query()->findOrFail($id);
            $review->delete();

            event(new DeletedContentEvent(REVIEW_MODULE_SCREEN_NAME, $request, $review));

            return $this
                ->httpResponse()
                ->setMessage(trans('core/base::notices.delete_success_message'))
                ->setData([
                    'next_url' => route('reviews.index'),
                ]);
        } catch (Exception $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function ajaxSearchCustomers(SelectSearchAjaxRequest $request)
    {
        $customers = Customer::query()
            ->where(function (Builder $query) use ($request) {
                $query
                    ->where('name', 'LIKE', "%{$request->input('search')}%")
                    ->orWhere('email', 'LIKE', "%{$request->input('search')}%");
            })
            ->select('id', 'name')
            ->paginate();

        return $this
            ->httpResponse()
            ->setData($customers);
    }

    public function ajaxSearchProducts(SelectSearchAjaxRequest $request)
    {
        $products = Product::query()
            ->wherePublished()
            ->where('is_variation', false)
            ->where('name', 'LIKE', "%{$request->input('search')}%")
            ->select('id', 'name')
            ->paginate();

        return $this
            ->httpResponse()
            ->setData($products);
    }
}
