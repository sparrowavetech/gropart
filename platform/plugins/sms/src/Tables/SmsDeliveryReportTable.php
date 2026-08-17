<?php

namespace Botble\Sms\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Sms\Models\SmsLog;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\BulkChanges\DateBulkChange;
use Botble\Table\BulkChanges\TextBulkChange;
use Botble\Table\Columns\Column;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Query\Builder as QueryBuilder;

class SmsDeliveryReportTable extends TableAbstract
{
    protected $hasOperations = false;

    protected string $filterTemplate = 'plugins/sms::delivery-report-filter';

    protected int $pageLength = 10;

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('sent_at', fn (SmsLog $item) => BaseHelper::formatDateTime($item->sent_at))
            ->editColumn('delivered_at', fn (SmsLog $item) => $item->delivered_at ? BaseHelper::formatDateTime($item->delivered_at) : '-')
            ->editColumn('message', fn (SmsLog $item) => BaseHelper::clean($item->message))
            ->editColumn('job_id', fn (SmsLog $item) => BaseHelper::clean((string) $item->job_id))
            ->editColumn('dlt_param', function (SmsLog $item) {
                return sprintf(
                    '<strong>PEID:</strong><br>%s<br><strong>TID:</strong><br>%s',
                    BaseHelper::clean($this->entityId()),
                    BaseHelper::clean((string) $item->template_id)
                );
            });

        return $this->toJson($data);
    }

    public function setup(): void
    {
        $this
            ->model(SmsLog::class)
            ->addColumns([
                Column::make('sent_at')->label(trans('plugins/sms::sms.delivery_report.sent_date')),
                Column::make('recipient')->label(trans('plugins/sms::sms.delivery_report.number')),
                Column::make('message')->label(trans('plugins/sms::sms.delivery_report.sms')),
                Column::make('status')->label(trans('plugins/sms::sms.delivery_report.status')),
                Column::make('delivered_at')->label(trans('plugins/sms::sms.delivery_report.delivered_date')),
                Column::make('job_id')->label(trans('plugins/sms::sms.delivery_report.job_id')),
                Column::make('dlt_param')->label(trans('plugins/sms::sms.delivery_report.dlt_param'))->orderable(false)->searchable(false),
            ])
            ->addBulkChanges([
                TextBulkChange::make()->name('recipient')->title(trans('plugins/sms::sms.delivery_report.number')),
                TextBulkChange::make()->name('template_id')->title(trans('plugins/sms::sms.delivery_report.tid')),
                TextBulkChange::make()->name('peid')->title(trans('plugins/sms::sms.delivery_report.peid')),
                DateBulkChange::make()->name('sent_from')->title(trans('plugins/sms::sms.delivery_report.sent_from')),
                DateBulkChange::make()->name('sent_to')->title(trans('plugins/sms::sms.delivery_report.sent_to')),
            ])
            ->queryUsing(fn (Builder $query) => $query->select([
                'id',
                'recipient',
                'message',
                'status',
                'template_id',
                'job_id',
                'sent_at',
                'delivered_at',
                'created_at',
            ])->latest('sent_at')->latest('id'))
            ->onFilterQuery(function (
                Builder|QueryBuilder|Relation $query,
                string $key,
                string $operator,
                ?string $value
            ) {
                if ($value === null || $value === '') {
                    return false;
                }

                return match ($key) {
                    'sent_from' => $query->whereDate('sent_at', '>=', $value),
                    'sent_to' => $query->whereDate('sent_at', '<=', $value),
                    'peid' => $this->entityId() === trim($value) ? $query : $query->whereRaw('1 = 0'),
                    default => false,
                };
            });
    }

    private function entityId(): string
    {
        $url = (string) setting('sms_url');
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return (string) ($query['PEID'] ?? '');
    }
}
