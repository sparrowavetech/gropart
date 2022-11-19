<?php

namespace Botble\Ecommerce\Tables;

use BaseHelper;
use Botble\Ecommerce\Repositories\Interfaces\EnquiryInterface;
use Botble\Table\Abstracts\TableAbstract;
use Html;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class EnquiryTable extends TableAbstract
{
    /**
     * @var bool
     */
    protected $hasActions = true;

    /**
     * @var bool
     */
    protected $hasFilter = true;

    /**
     * EnquiryTable constructor.
     * @param DataTables $table
     * @param UrlGenerator $urlGenerator
     * @param EnquiryInterface $brandRepository
     */
    public function __construct(DataTables $table, UrlGenerator $urlGenerator, EnquiryInterface $enquiryRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $enquiryRepository;
        $this->hasOperations = false;
        $this->hasActions = false;
        $this->hasCheckbox = false;
    }

    /**
     * {@inheritDoc}
     */
    public function ajax()
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('product', function ($item) {
                return  BaseHelper::clean($item->product->name);
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
            ->editColumn('address', function ($item) {
                return  BaseHelper::clean($item->address);
            })
            ->editColumn('city', function ($item) {
                return  BaseHelper::clean($item->cityName->name);
            })
            ->editColumn('state', function ($item) {
                return  BaseHelper::clean($item->stateName->name);
            })
            ->editColumn('zip_code', function ($item) {
                return  BaseHelper::clean($item->zip_code);
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at);
            });

        return $this->toJson($data);
    }

    /**
     * {@inheritDoc}
     */
    public function query()
    {
        $query = $this->repository->getModel()->select([
            'id',
            'product_id',
            'name',
            'email',
            'phone',
            'state',
            'city',
            'address',
            'zip_code',
            'created_at'
        ]);

        return $this->applyScopes($query);
    }

    /**
     * {@inheritDoc}
     */
    public function columns()
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
             'address'        => [
                'title' => trans('core/base::tables.address'),
                'width' => '100px',
                'class' => 'text-start',
            ],
            'city'        => [
                'title' => trans('core/base::tables.city'),
                'class' => 'text-start',
            ],
            'state'        => [
                'title' => trans('core/base::tables.state'),
                'class' => 'text-start',
            ],
            'zip_code'        => [
                'title' => trans('core/base::tables.zip_code'),
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
    public function renderTable($data = [], $mergeData = [])
    {
        if ($this->query()->count() === 0 &&
            !$this->request()->wantsJson() &&
            $this->request()->input('filter_table_id') !== $this->getOption('id') && !$this->request()->ajax()
        ) {
            return view('plugins/ecommerce::enquires.intro');
        }

        return parent::renderTable($data, $mergeData);
    }
}
