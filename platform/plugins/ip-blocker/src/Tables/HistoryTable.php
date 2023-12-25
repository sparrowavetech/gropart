<?php

namespace ArchiElite\IpBlocker\Tables;

use ArchiElite\IpBlocker\Models\History;
use ArchiElite\IpBlocker\Repositories\Interfaces\IpBlockerInterface;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\DataTables;
use Botble\Table\Supports\Builder;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class HistoryTable extends TableAbstract
{
    protected $view = 'plugins/ip-blocker::tables.simple-table';

    protected $hasActions = true;

    protected $hasCheckbox = true;

    protected $hasOperations = true;

    public function __construct(DataTables $table, UrlGenerator $urlGenerator, IpBlockerInterface $ipBlockerRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $ipBlockerRepository;

        if (! Auth::user()->hasPermission('ip-blocker.destroy')) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('checkbox', function (History $item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('updated_at', function (History $item) {
                return BaseHelper::formatDateTime($item->updated_at);
            })
            ->addColumn('operations', function (History $item) {
                return $this->getOperations(null, 'ip-blocker.destroy', $item);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->repository->getModel()->select([
            'id',
            'ip_address',
            'count_requests',
            'updated_at',
        ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            'id' => [
                'name' => 'id',
                'title' => trans('core/base::tables.id'),
                'width' => '20px',
            ],
            'ip_address' => [
                'title' => trans('plugins/ip-blocker::ip-blocker.ip_address'),
                'class' => 'text-start',
            ],
            'count_requests' => [
                'title' => trans('plugins/ip-blocker::ip-blocker.count_requests'),
                'class' => 'text-start',
            ],
            'updated_at' => [
                'title' => trans('plugins/ip-blocker::ip-blocker.last_visited'),
                'width' => 'text-start',
            ],
        ];
    }

    public function buttons(): array
    {
        return [
            'empty' => [
                'link' => route('ip-blocker.empty'),
                'text' => Html::tag('i', '', ['class' => 'fa fa-trash'])->toHtml() . ' ' . trans('plugins/ip-blocker::ip-blocker.delete_all'),
            ],
        ];
    }

    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('ip-blocker.deletes'), 'ip-blocker.destroy', parent::bulkActions());
    }
}
