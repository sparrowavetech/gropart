<?php

namespace SparroWave\RazorpayEasyEmi\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Marketplace\Tables\Traits\ForVendor;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use SparroWave\RazorpayEasyEmi\Models\RazorpayEmiVendorRequest;

class VendorEmiRequestTable extends TableAbstract
{
    use ForVendor;

    protected $hasOperations = false;

    public function setup(): void
    {
        $this
            ->model(RazorpayEmiVendorRequest::class)
            ->addActions([])
            ->addColumns([
                IdColumn::make(),
                Column::make('product_id')
                    ->title(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.product'))
                    ->alignStart(),
                Column::make('requested_scheme')
                    ->title(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.scheme'))
                    ->alignCenter(),
                Column::make('subvention_willingness')
                    ->title(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.subvention'))
                    ->alignCenter(),
                Column::make('status')
                    ->title(trans('core/base::tables.status'))
                    ->alignCenter()
                    ->width(120),
                Column::make('notes')
                    ->title(trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.notes'))
                    ->alignStart(),
                CreatedAtColumn::make(),
            ])
            ->queryUsing(function (Builder $query) {
                $storeId = auth('customer')->user()?->store?->id ?: 0;

                return $query
                    ->select([
                        'id',
                        'product_id',
                        'store_id',
                        'requested_scheme',
                        'subvention_willingness',
                        'notes',
                        'status',
                        'admin_remarks',
                        'approved_offer_id',
                        'created_at',
                    ])
                    ->with(['product'])
                    ->where('store_id', $storeId)
                    ->latest();
            });
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('product_id', function (RazorpayEmiVendorRequest $item) {
                if (! $item->product) {
                    return '&mdash;';
                }

                $name = BaseHelper::clean($item->product->name);
                $url = route('marketplace.vendor.products.edit', $item->product->id);

                return Html::link($url, $name, ['class' => 'fw-semibold text-primary text-decoration-none']);
            })
            ->editColumn('requested_scheme', function (RazorpayEmiVendorRequest $item) {
                return $item->requested_scheme ? $item->requested_scheme->toHtml() : '&mdash;';
            })
            ->editColumn('subvention_willingness', function (RazorpayEmiVendorRequest $item) {
                return $item->subvention_willingness ? $item->subvention_willingness->toHtml() : '&mdash;';
            })
            ->editColumn('status', function (RazorpayEmiVendorRequest $item) {
                return $item->status ? $item->status->toHtml() : '&mdash;';
            })
            ->editColumn('notes', function (RazorpayEmiVendorRequest $item) {
                $notes = BaseHelper::clean($item->notes);
                if ($item->admin_remarks) {
                    $notes .= '<br><small class="text-muted"><strong>' . trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.admin_remarks') . ':</strong> ' . BaseHelper::clean($item->admin_remarks) . '</small>';
                }
                if ($item->approved_offer_id) {
                    $notes .= '<br><small class="text-success"><strong>' . trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.offer_id') . ':</strong> ' . e($item->approved_offer_id) . '</small>';
                }

                return $notes;
            })
            ->filter(function ($query) {
                $keyword = $this->request->input('search.value');
                if ($keyword) {
                    return $query->where(function ($q) use ($keyword) {
                        $q->whereHas('product', function ($subQuery) use ($keyword) {
                            $subQuery->where('ec_products.name', 'LIKE', '%' . $keyword . '%');
                        })
                        ->orWhere('notes', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('admin_remarks', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('approved_offer_id', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('requested_scheme', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('status', 'LIKE', '%' . $keyword . '%');
                    });
                }

                return $query;
            });

        return $this->toJson($data);
    }
}

