<?php

namespace Botble\Location\Tables;

use Illuminate\Support\Facades\Auth;
use BaseHelper;
use Botble\Base\Enums\BaseStatusEnum;

use Botble\Location\Repositories\Interfaces\PincodeInterface;
use Botble\Location\Repositories\Interfaces\CountryInterface;
use Botble\Location\Repositories\Interfaces\StateInterface;

use Botble\Table\Abstracts\TableAbstract;
use Html;
use Illuminate\Contracts\Routing\UrlGenerator;
use Yajra\DataTables\DataTables;

class PincodeTable extends TableAbstract
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
     * @var CountryInterface
     */
    protected $countryRepository;

    /**
     * @var StateInterface
     */
    protected $stateRepository;

    protected $PincodeInterface;

    /**
     * CityTable constructor.
     * @param DataTables $table
     * @param UrlGenerator $urlGenerator
     * @param PincodeInterface $cityRepository
     * @param CountryInterface $countryRepository
     * @param StateInterface $stateRepository
     */
    public function __construct(
        DataTables $table,
        UrlGenerator $urlGenerator,
        PincodeInterface $PincodeInterface,
        CountryInterface $countryRepository,
        StateInterface $stateRepository
    ) {
        parent::__construct($table, $urlGenerator);

        $this->repository = $PincodeInterface;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;

        if (!Auth::user()->hasAnyPermission(['city.edit', 'city.destroy'])) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function ajax()
    {
        $data = $this->table
            ->eloquent($this->query())
            // ->editColumn('pincode', function ($item) {
            //     if (!Auth::user()->hasPermission('city.edit')) {
            //         return $item->name;
            //     }
            //     return $item->name;
            // })
            ->editColumn('city_id', function ($item) {
                if (!$item->city_id || !$item->city->name) {
                    return '&mdash;';
                }

                return Html::link(route('city.edit', $item->city_id), $item->city->name);
            })
            ->editColumn('country_id', function ($item) {
                if (!$item->country_id || !$item->country->name) {
                    return '&mdash;';
                }

                return Html::link(route('country.edit', $item->country_id), $item->country->name);
            })
            ->editColumn('checkbox', function ($item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at);
            })
            ->editColumn('status', function ($item) {
                // if ($item->status == 1) {
                //     return '&mdash;';
                // }
                return $item->status->toHtml();
            })
            ->addColumn('operations', function ($item) {
                return $this->getOperations('', 'pincode.destroy', $item);
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
            'pincode',
            'city_id',
            'created_at',
            'status',
        ]);

        return $this->applyScopes($query);
    }

    /**
     * {@inheritDoc}
     */
    public function columns()
    {
        return [
            'id' => [
                'title' => trans('core/base::tables.id'),
                'width' => '20px',
            ],
            'pincode'       => [
                'title' => 'Pincode',
                'class' => 'text-start',
            ],
            'city_id'   => [
                'title' => 'City',
                'class' => 'text-start',
            ],
            // 'country_id' => [
            //     'title' => trans('plugins/location::city.country'),
            //     'class' => 'text-start',
            // ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'width' => '100px',
            ],
            'status'     => [
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function buttons()
    {
        return $this->addCreateButton(route('pincode.create'), 'city.create');
    }

    /**
     * {@inheritDoc}
     */
    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('pincode.deletes'), 'city.destroy', parent::bulkActions());
    }

    /**
     * {@inheritDoc}
     */
    public function getBulkChanges(): array
    {
        return [
       
            'status'     => [
                'title'    => trans('core/base::tables.status'),
                'type'     => 'customSelect',
                'choices'  => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
        ];
    }
}
