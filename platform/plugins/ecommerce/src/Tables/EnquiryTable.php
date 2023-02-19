<?php

namespace Botble\Ecommerce\Tables;

use BaseHelper;
use Botble\Ecommerce\Enums\EnquiryStatusEnum;
use Botble\Ecommerce\Repositories\Interfaces\EnquiryInterface;
use Botble\Table\Abstracts\TableAbstract;
use Html;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\DataTables;

class EnquiryTable extends TableAbstract
{
    protected $hasActions = true;

    protected $hasFilter = true;

    public function __construct(DataTables $table, UrlGenerator $urlGenerator, EnquiryInterface $enquiryRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $enquiryRepository;

        if (! Auth::user()->hasAnyPermission(['brands.edit', 'brands.destroy'])) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('product', function ($item) {
                return Html::link(route('products.edit', $item->product_id), BaseHelper::clean($item->product->name));

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
                return $this->getOperations('enquires.edit', 'enquires.destroy', $item);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->repository->getModel()->select([
            'id',
            'product_id',
            'name',
            'email',
            'phone',
            'status',
            'created_at'
        ]);

        return $this->applyScopes($query);
    }

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



    public function bulkActions(): array
    {
        return $this->addDeleteAction(
            route('enquires.deletes'),
            'enquires.destroy',
            parent::bulkActions()
        );
    }

    public function getBulkChanges(): array
    {
        return [
            'name'       => [
                'title'    => trans('core/base::tables.name'),
                'type'     => 'text',
                'validate' => 'required|max:120',
            ],
            'status'     => [
                'title'    => trans('core/base::tables.status'),
                'type'     => 'select',
                'choices'  => EnquiryStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', EnquiryStatusEnum::values()),
            ],

            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type'  => 'date',
            ],
        ];
    }

    public function renderTable($data = [], $mergeData = []): View|Factory|Response
    {
        if ($this->query()->count() === 0 &&
            ! $this->request()->wantsJson() &&
            $this->request()->input('filter_table_id') !== $this->getOption('id') && ! $this->request()->ajax()
        ) {
            return view('plugins/ecommerce::enquires.intro');
        }

        return parent::renderTable($data, $mergeData);
    }
    public function getDefaultButtons(): array
    {
        return [
            'export',
            'reload',
        ];
    }
}
