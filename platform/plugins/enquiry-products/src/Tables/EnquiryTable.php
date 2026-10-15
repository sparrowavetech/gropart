<?php
namespace SparroWave\EnquiryProducts\Tables;

use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\{EditAction, DeleteAction, ViewAction};
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\{NameBulkChange, EmailBulkChange, PhoneBulkChange, StatusBulkChange, CreatedAtBulkChange};
use Botble\Table\Columns\{NameColumn, EmailColumn, PhoneColumn, CreatedAtColumn, Column, FormattedColumn};
use Illuminate\Database\Eloquent\Builder;
use SparroWave\EnquiryProducts\Models\Enquiry;

class EnquiryTable extends TableAbstract
{
    protected string $exportClass = \Botble\Table\Supports\TableExportHandler::class;

    public function setup(): void
    {
        $vendor = $this instanceof VendorEnquiryTable;
        $route = $vendor ? 'enquiry-products.vendor.show' : 'enquiry-products.show';
        $this->model(Enquiry::class)->addColumns([
            NameColumn::make('code')->title(__('plugins/enquiry-products::messages.reference'))
                ->urlUsing(fn (NameColumn $column) => route($route, $column->getItem()->id)),
            Column::make('name')->title(__('plugins/enquiry-products::messages.customer')),
            EmailColumn::make()->linkable()->withEmptyState(),
            PhoneColumn::make()->linkable()->withEmptyState(),
            NameColumn::make('product_id')->title(__('plugins/enquiry-products::messages.equipment'))->orderable(false)->searchable(false)->externalLink()
                ->urlUsing(function (NameColumn $column): ?string {
                    $slug = $column->getItem()->product?->slugable?->key;
                    return $slug ? route('public.enquiry-products.show', $slug) : null;
                })
                ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->product?->name ?? __('plugins/enquiry-products::messages.deleted_product')),
            FormattedColumn::make('status')->title(__('plugins/enquiry-products::messages.status'))
                ->renderUsing(function (FormattedColumn $column, $value): string {
                    $color = match ((string) $value) {
                        'contacted' => 'success',
                        'pending' => 'info',
                        'rejected' => 'danger',
                        'not_available', 'not_interested', 'not_intrested' => 'warning',
                        default => 'secondary',
                    };
                    $label = in_array($value, ['not_interested', 'not_intrested'], true)
                        ? __('plugins/enquiry-products::messages.not_interested') : __('plugins/enquiry-products::messages.' . (string) $value);
                    return '<span class="badge bg-' . $color . ' text-white">' . e($label) . '</span>';
                }),
            CreatedAtColumn::make(),
        ])->queryUsing(function (Builder $query) use ($vendor) {
            if ($vendor) {
                $customer = auth('customer')->user();
                abort_unless($customer && $customer->is_vendor && $customer->store?->id, 403);
                $query->where('store_id', $customer->store->id);
            }
            return $query->with('product.slugable')->select('ec_product_enquiry.*');
        });
        if ($vendor) {
            $this->addActions([ViewAction::make()->route($route)]);
        } else {
            $this->addActions([
                EditAction::make()->route($route)->permission('enquiry-products.edit'),
                DeleteAction::make()->route('enquiry-products.destroy')->permission('enquiry-products.destroy'),
            ])->addBulkActions([DeleteBulkAction::make()->permission('enquiry-products.destroy')]);
        }
    }

    public function getFilters(): array
    {
        return collect([
            NameBulkChange::make(), EmailBulkChange::make(), PhoneBulkChange::make(),
            StatusBulkChange::make()->choices(array_combine(Enquiry::STATUSES, array_map(fn ($status) => __('plugins/enquiry-products::messages.' . $status), Enquiry::STATUSES))),
            CreatedAtBulkChange::make(),
        ])->mapWithKeys(fn ($filter) => [$filter->getName() => $filter->toArray()])->all();
    }

    public function getDefaultButtons(): array
    {
        return array_unique(array_merge(['export'], parent::getDefaultButtons()));
    }
}
