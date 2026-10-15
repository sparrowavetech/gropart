<?php
namespace SparroWave\EnquiryProducts\Tables;

use Botble\Marketplace\Models\Store;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\{NameColumn, EmailColumn, PhoneColumn, Column, StatusColumn};
use Botble\Table\BulkChanges\{NameBulkChange, EmailBulkChange, PhoneBulkChange};
use Illuminate\Database\Eloquent\Builder;
use SparroWave\EnquiryProducts\Support\{Catalog, StoreUrl};

class EnquirySellerTable extends TableAbstract
{
    public function setup(): void
    {
        abort_unless(is_plugin_active('marketplace'), 404);
        $this->model(Store::class)->addActions([
            EditAction::make()->route('marketplace.store.edit')->permission('marketplace.store.edit'),
        ])->addColumns([
            NameColumn::make('name')->title(__('plugins/enquiry-products::messages.seller'))
                ->externalLink()->urlUsing(fn (NameColumn $column) => StoreUrl::catalogue($column->getItem())),
            EmailColumn::make()->linkable()->withEmptyState(),
            PhoneColumn::make()->linkable()->withEmptyState(),
            Column::make('enquiry_products_count')->title(__('plugins/enquiry-products::messages.published_enquiry_products'))->searchable(false),
            StatusColumn::make(),
        ])->queryUsing(function (Builder $query) {
            $products = app(Catalog::class)->products()->whereColumn('ec_products.store_id', 'mp_stores.id');
            return $query->select('mp_stores.*')->with('slugable')
                ->whereExists((clone $products)->selectRaw('1'))
                ->selectSub((clone $products)->selectRaw('COUNT(*)'), 'enquiry_products_count');
        });
    }

    public function getFilters(): array
    {
        return collect([NameBulkChange::make(), EmailBulkChange::make(), PhoneBulkChange::make()])
            ->mapWithKeys(fn ($filter) => [$filter->getName() => $filter->toArray()])->all();
    }
}
