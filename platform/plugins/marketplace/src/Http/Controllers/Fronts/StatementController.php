<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Marketplace\Repositories\Interfaces\RevenueInterface;
use Botble\Marketplace\Repositories\Interfaces\StoreInterface;
use Botble\Marketplace\Tables\StoreRevenueTable;
use Botble\Table\Abstracts\TableAbstract;
use Illuminate\Http\Request;
use MarketplaceHelper;

class StatementController extends BaseController
{
    protected StoreInterface $storeRepository;

    protected RevenueInterface $revenueRepository;

    public function __construct(StoreInterface $storeRepository, RevenueInterface $revenueRepository)
    {
        $this->storeRepository = $storeRepository;
        $this->revenueRepository = $revenueRepository;
    }

    public function index(StoreRevenueTable $table, Request $request)
    {
        page_title()->setTitle(__('Statements'));

        $request->route()->setParameter('id', auth('customer')->id());

        $table
            ->setType(TableAbstract::TABLE_TYPE_ADVANCED)
            ->setView('core/table::table');

        return $table->render(MarketplaceHelper::viewPath('dashboard.table.base'));
    }
}
