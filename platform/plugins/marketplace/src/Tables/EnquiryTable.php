<?php

namespace Botble\Marketplace\Tables;

use Html;
use BaseHelper;
use EcommerceHelper;
use MarketplaceHelper;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Botble\Table\Abstracts\TableAbstract;
use Illuminate\Database\Eloquent\Builder;
use Botble\Ecommerce\Enums\EnquiryStatusEnum;
use Illuminate\Contracts\Routing\UrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Botble\Ecommerce\Repositories\Interfaces\EnquiryInterface;

class EnquiryTable extends TableAbstract
{
    /**
     * @var bool
     */
    protected $hasActions = false;

    /**
     * @var bool
     */
    protected $hasFilter = false;

    /**
     * @var bool
     */
    protected $hasCheckbox = false;

    /**
     * OrderTable constructor.
     * @param DataTables $table
     * @param UrlGenerator $urlGenerator
     * @param EnquiryInterface $orderRepository
     */
    public function __construct(DataTables $table, UrlGenerator $urlGenerator, EnquiryInterface $enquiryRepository)
    {
        $this->repository = $enquiryRepository;
        parent::__construct($table, $urlGenerator);
    }

     /**
     * {@inheritDoc}
     */
    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('product', function ($item) {
                return Html::link(route('marketplace.vendor.products.edit', $item->product_id), BaseHelper::clean($item->product->name));
               
            })
            ->editColumn('checkbox', function ($item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('name', function ($item) {
                return  BaseHelper::clean($item->name);
            })
            ->editColumn('email', function ($item) {
                return  BaseHelper::clean($item->email);
            })
            ->editColumn('phone', function ($item) {
                return  BaseHelper::clean($item->phone);
            })
            ->editColumn('status', function ($item) {
                return BaseHelper::clean($item->status->toHtml());
            })
            ->editColumn('store', function ($item) {
                return BaseHelper::clean($item->product->store->name);
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at);
            })->addColumn('operations', function ($item) {
                return $this->getOperations('marketplace.vendor.enquiries.edit', 'enquires.destroy', $item);
            });

        return $this->toJson($data);
    }

    /**
     * {@inheritDoc}
     */
    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->repository->getModel()
            ->select([
                'id',
                'product_id',
                'name',
                'email',
                'phone',
                'status',
                'created_at'
            ])
            ->with(['product'])
            ->where('store_id', auth('customer')->user()->store->id);

        return $this->applyScopes($query);
    }

     /**
     * {@inheritDoc}
     */
    public function columns(): array
    {
        return [
            'id'          => [
                'title' => trans('core/base::tables.id'),
                'width' => '20px',
                'class' => 'text-start',
            ],
            'product'        => [
                'title' => trans('core/base::tables.product_name'),
                'class' => 'text-start',
            ],
            'name'        => [
                'title' => trans('core/base::tables.name'),
                'class' => 'text-start',
            ],
            'email'        => [
                'title' => trans('core/base::tables.email'),
                'class' => 'text-start',
            ],
            'phone'        => [
                'title' => trans('core/base::tables.phone'),
                'class' => 'text-start',
            ],
            'status'        => [
                'title' => trans('core/base::tables.status'),
                'class' => 'text-start',
            ],
            'store'        => [
                'title' => trans('core/base::tables.store'),
                'class' => 'text-start',
            ],
            'created_at'  => [
                'title' => trans('core/base::tables.created_at'),
                'width' => '100px',
                'class' => 'text-start',
            ],
           
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }
}
