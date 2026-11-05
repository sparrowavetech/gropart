<?php

namespace SparroWave\SellerInvoicing\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Form;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\ViewAction;
use Botble\Table\Columns\CheckboxColumn;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use SparroWave\SellerInvoicing\Models\Document;

class SellerInvoiceTable extends TableAbstract
{
    public function setup(): void
    {
        $this->defaultSortColumnName = 'order_id';

        $this
            ->model(Document::class)
            ->defaultSortingUsing(function () {
                return [
                    [
                        3, // order_id column
                        'desc',
                    ],
                ];
            })
            ->addActions([
                ViewAction::make()
                    ->route('seller-invoicing.show')
                    ->permission('seller-invoicing.show'),
                Action::make('download')
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.download_pdf'))
                    ->icon('ti ti-download')
                    ->color('success')
                    ->url(fn (Action $action) => $action->getItem()?->download_url ?: route('seller-invoicing.download', $action->getItem()?->getKey()))
                    ->permission('seller-invoicing.show'),
                Action::make('print')
                    ->label(trans('plugins/seller-invoicing::seller-invoicing.print'))
                    ->icon('ti ti-printer')
                    ->color('secondary')
                    ->url(fn (Action $action) => $action->getItem()?->print_url ?: route('seller-invoicing.print', $action->getItem()?->getKey()))
                    ->addAttribute('target', '_blank')
                    ->permission('seller-invoicing.show'),
            ]);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->addColumn('checkbox', function ($item) {
                return view('core/table::partials.checkbox', ['id' => $item->getKey()])->render();
            })
            ->orderColumn('order_date', function ($query, $order) {
                $query->orderBy('ec_orders.created_at', $order);
            })
            ->orderColumn('order_id', function ($query, $order) {
                $query->orderBy('si_documents.order_id', $order);
            })
            ->orderColumn('issued_at', function ($query, $order) {
                $query->orderBy('si_documents.issued_at', $order);
            })
            ->filter(function ($query) {
                $keyword = $this->request->input('search.value');
                if ($keyword) {
                    $clean = ltrim($keyword, '#');
                    $query->where(function ($subQuery) use ($keyword, $clean) {
                        $subQuery->where('si_documents.number', 'LIKE', '%' . $keyword . '%')
                            ->orWhere('si_documents.id', 'LIKE', '%' . $clean . '%')
                            ->orWhere('si_documents.status', 'LIKE', '%' . $keyword . '%')
                            ->orWhereHas('order', function ($orderQuery) use ($keyword, $clean) {
                                $orderQuery->where('code', 'LIKE', '%' . $keyword . '%')
                                    ->orWhere('code', 'LIKE', '%' . $clean . '%')
                                    ->orWhere('id', 'LIKE', '%' . $clean . '%')
                                    ->orWhereHas('store', function ($storeQuery) use ($keyword) {
                                        $storeQuery->where('name', 'LIKE', '%' . $keyword . '%');
                                    });
                            })
                            ->orWhereHas('seller', function ($sellerQuery) use ($keyword) {
                                $sellerQuery->where('name', 'LIKE', '%' . $keyword . '%');
                            });
                    });
                }

                return $query;
            });

        return $this->toJson($data);
    }

    public function query(): EloquentRelation|EloquentBuilder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'si_documents.*',
                'ec_orders.created_at as order_created_at',
            ])
            ->leftJoin('ec_orders', 'ec_orders.id', '=', 'si_documents.order_id')
            ->with(['order.store', 'seller']);

        $dateFrom = $this->request->input('date_from');
        $dateTo = $this->request->input('date_to');
        $dateType = $this->request->input('date_type', 'order_date');

        if ($dateFrom || $dateTo) {
            $col = $dateType === 'issued_at' ? 'si_documents.issued_at' : 'ec_orders.created_at';
            if ($dateFrom) {
                $query->whereDate($col, '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate($col, '<=', $dateTo);
            }
        }

        $query
            ->orderByDesc('ec_orders.created_at')
            ->orderByDesc('si_documents.order_id');

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            CheckboxColumn::make(),
            IdColumn::make(),
            FormattedColumn::make('number')
                ->title(trans('plugins/seller-invoicing::seller-invoicing.invoice_number'))
                ->alignStart()
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();
                    $url = route('seller-invoicing.show', $item->getKey());

                    return sprintf(
                        '<a href="%s" class="fw-bold text-primary text-decoration-none">%s</a>',
                        e($url),
                        e($item->number)
                    );
                }),
            FormattedColumn::make('order_id')
                ->title(trans('plugins/seller-invoicing::seller-invoicing.order_number'))
                ->alignStart()
                ->orderable(true)
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();
                    if (! $item->order_id) {
                        return '&mdash;';
                    }

                    $code = '#' . ltrim($item->order?->code ?: (string) $item->order_id, '#');
                    $url = route('orders.edit', $item->order_id);

                    return sprintf(
                        '<a href="%s" target="_blank" class="fw-semibold text-primary text-decoration-none">%s <i class="ti ti-external-link"></i></a>',
                        e($url),
                        e($code)
                    );
                }),
            FormattedColumn::make('order_date')
                ->title(trans('plugins/seller-invoicing::seller-invoicing.order_date'))
                ->alignStart()
                ->orderable(true)
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();
                    $date = $item->order?->created_at ?: $item->order_created_at;

                    return $date ? BaseHelper::formatDate($date) : '&mdash;';
                }),
            FormattedColumn::make('issued_at')
                ->title(trans('plugins/seller-invoicing::seller-invoicing.invoice_date'))
                ->alignStart()
                ->orderable(true)
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();
                    $date = $item->issued_at ?: $item->created_at;

                    return $date ? BaseHelper::formatDate($date) : '&mdash;';
                }),
            FormattedColumn::make('seller_id')
                ->title(trans('plugins/seller-invoicing::seller-invoicing.vendor_store'))
                ->alignStart()
                ->orderable(false)
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();
                    $storeName = $item->order?->store?->name ?: ($item->seller?->name ?: '&mdash;');

                    return e($storeName);
                }),
            FormattedColumn::make('subtotal')
                ->title(trans('plugins/seller-invoicing::seller-invoicing.subtotal'))
                ->alignEnd()
                ->renderUsing(function (FormattedColumn $column) {
                    return format_price($column->getItem()->subtotal);
                }),
            FormattedColumn::make('tax_amount')
                ->title(trans('plugins/seller-invoicing::seller-invoicing.tax'))
                ->alignEnd()
                ->renderUsing(function (FormattedColumn $column) {
                    return format_price($column->getItem()->tax_amount);
                }),
            FormattedColumn::make('grand_total')
                ->title(trans('plugins/seller-invoicing::seller-invoicing.grand_total'))
                ->alignEnd()
                ->renderUsing(function (FormattedColumn $column) {
                    return '<strong class="text-success">' . format_price($column->getItem()->grand_total) . '</strong>';
                }),
            FormattedColumn::make('status')
                ->title(trans('core/base::tables.status'))
                ->alignCenter()
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();
                    $status = strtolower($item->status);
                    $badgeClass = match ($status) {
                        'issued' => 'bg-success-lt text-success border border-success',
                        'pending' => 'bg-warning-lt text-warning border border-warning',
                        'canceled', 'cancelled' => 'bg-danger-lt text-danger border border-danger',
                        default => 'bg-secondary-lt text-secondary border border-secondary',
                    };

                    return sprintf(
                        '<span class="badge %s">%s</span>',
                        $badgeClass,
                        e(ucfirst($item->status))
                    );
                }),
        ];
    }

    public function buttons(): array
    {
        $buttons = [];

        $buttons['download-invoices'] = [
            'text' => BaseHelper::renderIcon('ti ti-download') . ' ' . trans('plugins/seller-invoicing::seller-invoicing.download_invoices'),
            'class' => 'btn-primary btn-download-selected-invoices d-none',
        ];

        if ($this->hasPermission('seller-invoicing.settings')) {
            $buttons['settings'] = [
                'link' => route('seller-invoicing.settings'),
                'text' => BaseHelper::renderIcon('ti ti-settings') . ' ' . trans('plugins/seller-invoicing::seller-invoicing.settings.title'),
                'class' => 'btn-outline-primary',
            ];
        }

        return $buttons;
    }

    public function getValueInput(?string $title, ?string $value, ?string $type, array $data = []): array
    {
        if (in_array($type, ['date', 'datePicker'])) {
            $inputName = empty($title) ? 'filter_values[]' : 'value';
            $attributes = [
                'class' => 'form-control input-value filter-column-value',
                'placeholder' => 'YYYY-MM-DD',
                'autocomplete' => 'off',
            ];
            $html = Form::input('date', $inputName, $value, $attributes)->toHtml();

            return compact('html', 'data');
        }

        return parent::getValueInput($title, $value, $type, $data);
    }

    public function getFilters(): array
    {
        $stores = [];
        if (is_plugin_active('marketplace')) {
            $stores = \Botble\Marketplace\Models\Store::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        return [
            'number' => [
                'title' => trans('plugins/seller-invoicing::seller-invoicing.invoice_number'),
                'type' => 'text',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => [
                    'issued' => trans('plugins/seller-invoicing::seller-invoicing.issued'),
                    'pending' => trans('plugins/seller-invoicing::seller-invoicing.pending'),
                    'canceled' => trans('plugins/seller-invoicing::seller-invoicing.canceled'),
                ],
            ],
            'order_code' => [
                'title' => trans('plugins/seller-invoicing::seller-invoicing.order_number'),
                'type' => 'text',
            ],
            'store_id' => [
                'title' => trans('plugins/seller-invoicing::seller-invoicing.vendor_store'),
                'type' => 'select',
                'choices' => ['' => trans('core/base::tables.all')] + $stores,
            ],
            'order_date' => [
                'title' => trans('plugins/seller-invoicing::seller-invoicing.order_date'),
                'type' => 'datePicker',
            ],
            'issued_at' => [
                'title' => trans('plugins/seller-invoicing::seller-invoicing.invoice_date'),
                'type' => 'datePicker',
            ],
        ];
    }

    public function applyFilterCondition(
        EloquentBuilder|QueryBuilder|EloquentRelation $query,
        string $key,
        string $operator,
        ?string $value
    ) {
        if ($value === null || $value === '') {
            return $query;
        }

        if ($key === 'order_code') {
            $clean = ltrim($value, '#');

            return $query->whereHas('order', function ($q) use ($operator, $clean) {
                $q->where('code', $operator === 'like' ? 'LIKE' : '=', $operator === 'like' ? "%{$clean}%" : $clean)
                    ->orWhere('id', $clean);
            });
        }

        if ($key === 'store_id' || $key === 'store_name') {
            return $query->where(function ($q) use ($value) {
                $q->whereHas('order', function ($oq) use ($value) {
                    $oq->where('store_id', $value);
                })->orWhereHas('order.store', function ($sq) use ($value) {
                    $sq->where('id', $value)->orWhere('name', 'LIKE', "%{$value}%");
                })->orWhereHas('seller', function ($sq) use ($value) {
                    $sq->where('name', 'LIKE', "%{$value}%");
                });

                if (is_numeric($value)) {
                    $store = \Botble\Marketplace\Models\Store::query()->find($value);
                    if ($store && $store->customer_id) {
                        $q->orWhere('si_documents.seller_id', $store->customer_id);
                    }
                }
            });
        }

        if ($key === 'order_date') {
            $formattedDate = BaseHelper::formatDate($value, 'Y-m-d');

            return $query->whereDate('ec_orders.created_at', $operator === 'like' ? '=' : $operator, $formattedDate);
        }

        if ($key === 'issued_at') {
            $formattedDate = BaseHelper::formatDate($value, 'Y-m-d');

            return $query->whereDate('si_documents.issued_at', $operator === 'like' ? '=' : $operator, $formattedDate);
        }

        return parent::applyFilterCondition($query, $key, $operator, $value);
    }
}

