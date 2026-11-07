<?php

namespace SparroWave\FarmartHelper\Providers;

use Botble\Base\Facades\AdminHelper;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Tables\OrderIncompleteTable;

class OrderTableEnhancementServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        // 1. Exclude incomplete orders without customer details from OrderIncompleteTable
        add_filter(BASE_FILTER_TABLE_QUERY, function ($query, $table) {
            if ($table instanceof OrderIncompleteTable) {
                $query->where(function ($q): void {
                    $q->where(function ($sub): void {
                        $sub->whereNotNull('ec_orders.user_id')
                            ->where('ec_orders.user_id', '>', 0);
                    })->orWhereHas('address', function ($sub): void {
                        $sub->whereNotNull('name')
                            ->where('name', '!=', '');
                    });
                });
            }

            return $query;
        }, 120, 2);

        // 2. Responsive Table layout for Orders, Incomplete Orders, and Products tables
        add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function (?string $html): ?string {
            if (! AdminHelper::isInAdmin()) {
                return $html;
            }

            $req = request();
            $isTargetPage = $req && (
                $req->routeIs('orders.index')
                || $req->routeIs('orders.incomplete-list')
                || $req->routeIs('products.index')
                || (method_exists($req, 'is') && (
                    $req->is('*ecommerce/orders*')
                    || $req->is('*ecommerce/incomplete-orders*')
                    || $req->is('*ecommerce/products*')
                ))
            );

            if (! $isTargetPage) {
                return $html;
            }

            $styles = <<<HTML
<style id="farmart-order-table-responsive-styles">
/* Strict window/page container containment so low-res screens NEVER develop window horizontal scroll */
html,
body {
    max-width: 100vw !important;
    overflow-x: clip !important;
}

.page,
.page-wrapper {
    max-width: 100vw !important;
    overflow-x: clip !important;
    min-width: 0 !important;
}

@media (min-width: 992px) {
    .page-wrapper {
        max-width: calc(100vw - 240px) !important;
        width: calc(100vw - 240px) !important;
        min-width: 0 !important;
    }
}

.page-body,
.page-body > .container-xl,
.page-body > .container-fluid,
.table-wrapper,
.card,
.card:has(.card-table),
.card-table {
    max-width: 100% !important;
    min-width: 0 !important;
}

/* Force table-responsive to contain wide tables and provide smooth horizontal scrolling */
.card-table > .table-responsive,
.table-wrapper .table-responsive,
.table-responsive {
    max-width: 100% !important;
    width: 100% !important;
    min-width: 0 !important;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
}

/* Custom sleek scrollbar for table container */
.table-responsive::-webkit-scrollbar {
    height: 7px;
}
.table-responsive::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Date Range Filter Card protection */
.si-date-range-filter-card {
    max-width: 100% !important;
    min-width: 0 !important;
}

/* Compact padding for table cells across Order & Product tables */
#botble-ecommerce-tables-order-table_wrapper table.dataTable,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable,
#botble-ecommerce-tables-product-table_wrapper table.dataTable {
    width: 100% !important;
}

#botble-ecommerce-tables-order-table_wrapper table.dataTable th,
#botble-ecommerce-tables-order-table_wrapper table.dataTable td,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable th,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable td,
#botble-ecommerce-tables-product-table_wrapper table.dataTable th,
#botble-ecommerce-tables-product-table_wrapper table.dataTable td {
    padding: 0.45rem 0.5rem !important;
    vertical-align: middle !important;
}

/* Orders Table: Payment Method Column (wrap long gateway descriptions) */
#botble-ecommerce-tables-order-table_wrapper table.dataTable th.column-key-payment_method,
#botble-ecommerce-tables-order-table_wrapper table.dataTable td.column-key-payment_method,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable th.column-key-payment_method,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable td.column-key-payment_method {
    white-space: normal !important;
    max-width: 150px !important;
    min-width: 120px !important;
    font-size: 0.82rem !important;
    line-height: 1.25 !important;
    word-break: break-word !important;
}

/* Orders Table: Customer / Email Column */
#botble-ecommerce-tables-order-table_wrapper table.dataTable th.column-key-user_id,
#botble-ecommerce-tables-order-table_wrapper table.dataTable td.column-key-user_id,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable th.column-key-user_id,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable td.column-key-user_id {
    max-width: 180px !important;
    min-width: 130px !important;
    word-break: break-word !important;
    font-size: 0.84rem !important;
    line-height: 1.3 !important;
}

/* Products Table: Product Name Column */
#botble-ecommerce-tables-product-table_wrapper table.dataTable th.column-key-name,
#botble-ecommerce-tables-product-table_wrapper table.dataTable td.column-key-name {
    min-width: 180px !important;
    max-width: 260px !important;
    white-space: normal !important;
    word-break: break-word !important;
    font-size: 0.85rem !important;
    line-height: 1.3 !important;
}

/* Numeric & compact status columns */
#botble-ecommerce-tables-order-table_wrapper table.dataTable th.column-key-amount,
#botble-ecommerce-tables-order-table_wrapper table.dataTable td.column-key-amount,
#botble-ecommerce-tables-order-table_wrapper table.dataTable th.column-key-tax_amount,
#botble-ecommerce-tables-order-table_wrapper table.dataTable td.column-key-tax_amount,
#botble-ecommerce-tables-order-table_wrapper table.dataTable th.column-key-shipping_amount,
#botble-ecommerce-tables-order-table_wrapper table.dataTable td.column-key-shipping_amount,
#botble-ecommerce-tables-product-table_wrapper table.dataTable th.column-key-price,
#botble-ecommerce-tables-product-table_wrapper table.dataTable td.column-key-price,
#botble-ecommerce-tables-product-table_wrapper table.dataTable th.column-key-quantity,
#botble-ecommerce-tables-product-table_wrapper table.dataTable td.column-key-quantity,
#botble-ecommerce-tables-product-table_wrapper table.dataTable th.column-key-sku,
#botble-ecommerce-tables-product-table_wrapper table.dataTable td.column-key-sku,
#botble-ecommerce-tables-product-table_wrapper table.dataTable th.column-key-order,
#botble-ecommerce-tables-product-table_wrapper table.dataTable td.column-key-order {
    white-space: nowrap !important;
    font-size: 0.83rem !important;
}

/* Status Badges */
#botble-ecommerce-tables-order-table_wrapper table.dataTable td.column-key-payment_status .badge,
#botble-ecommerce-tables-order-table_wrapper table.dataTable td.column-key-status .badge,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable td.column-key-payment_status .badge,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable td.column-key-status .badge,
#botble-ecommerce-tables-product-table_wrapper table.dataTable td.column-key-status .badge,
#botble-ecommerce-tables-product-table_wrapper table.dataTable td.column-key-stock_status .badge {
    font-size: 0.75rem !important;
    padding: 0.25rem 0.45rem !important;
    white-space: nowrap !important;
}

/* Created At Column */
#botble-ecommerce-tables-order-table_wrapper table.dataTable th.column-key-created_at,
#botble-ecommerce-tables-order-table_wrapper table.dataTable td.column-key-created_at,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable th.column-key-created_at,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable td.column-key-created_at,
#botble-ecommerce-tables-product-table_wrapper table.dataTable th.column-key-created_at,
#botble-ecommerce-tables-product-table_wrapper table.dataTable td.column-key-created_at {
    white-space: nowrap !important;
    font-size: 0.82rem !important;
    min-width: 95px !important;
}

/* Operations Column */
#botble-ecommerce-tables-order-table_wrapper table.dataTable th.column-key-operations,
#botble-ecommerce-tables-order-table_wrapper table.dataTable td.column-key-operations,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable th.column-key-operations,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable td.column-key-operations,
#botble-ecommerce-tables-product-table_wrapper table.dataTable th.column-key-operations,
#botble-ecommerce-tables-product-table_wrapper table.dataTable td.column-key-operations {
    white-space: nowrap !important;
    width: 85px !important;
    min-width: 85px !important;
    text-align: center !important;
}

#botble-ecommerce-tables-order-table_wrapper table.dataTable td.column-key-operations .table-actions,
#botble-ecommerce-tables-order-incomplete-table_wrapper table.dataTable td.column-key-operations .table-actions,
#botble-ecommerce-tables-product-table_wrapper table.dataTable td.column-key-operations .table-actions {
    display: inline-flex !important;
    gap: 4px !important;
    flex-wrap: nowrap !important;
}
</style>
HTML;

            return ($html ?? '') . "\n" . $styles;
        }, 130);
    }
}

