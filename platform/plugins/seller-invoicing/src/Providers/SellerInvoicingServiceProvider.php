<?php

namespace SparroWave\SellerInvoicing\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\Models\Order;
use Botble\Marketplace\Forms\Settings\MarketplaceSettingForm;
use SparroWave\SellerInvoicing\Hooks\MarketplaceSettingHookListener;
use SparroWave\SellerInvoicing\Models\Document;
use SparroWave\SellerInvoicing\Services\GenerateSellerInvoiceService;
use SparroWave\SellerInvoicing\Services\InvoiceGeneratorService;
use SparroWave\SellerInvoicing\Supports\DependencyCheck;
use Throwable;

class SellerInvoicingServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    protected function isReady(): bool
    {
        if (class_exists(DependencyCheck::class)) {
            return DependencyCheck::ready();
        }

        $dependencyCheckFile = dirname(__DIR__) . '/Supports/DependencyCheck.php';
        if (file_exists($dependencyCheckFile)) {
            require_once $dependencyCheckFile;
            if (class_exists(DependencyCheck::class)) {
                return DependencyCheck::ready();
            }
        }

        return function_exists('is_plugin_active')
            && is_plugin_active('ecommerce')
            && is_plugin_active('marketplace')
            && class_exists(Order::class)
            && class_exists(\Botble\Marketplace\Models\Revenue::class);
    }

    public function boot(): void
    {
        if (! $this->isReady()) {
            return;
        }

        $this
            ->setNamespace('plugins/seller-invoicing')
            ->loadAndPublishConfigurations(['permissions', 'email'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web']);

        $this->app->booted(function (): void {
            \Botble\Base\Facades\EmailHandler::addTemplateSettings(
                'seller-invoicing',
                config('plugins.seller-invoicing.email', [])
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                \SparroWave\SellerInvoicing\Commands\CompileMonthlyGstCommand::class,
                \SparroWave\SellerInvoicing\Commands\SendMonthlyGstReportNotificationCommand::class,
            ]);

            $this->app->booted(function (): void {
                $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
                $schedule->command('seller-invoicing:compile-monthly-gst --notify')
                    ->monthlyOn(1, '02:00')
                    ->withoutOverlapping();
            });
        }

        DashboardMenu::beforeRetrieving(function (): void {
            DashboardMenu::make()->registerItem([
                'id' => 'sparrowave-seller-invoicing',
                'priority' => 15,
                'parent_id' => 'cms-plugins-marketplace',
                'name' => 'plugins/seller-invoicing::seller-invoicing.library',
                'icon' => 'ti ti-file-invoice',
                'url' => fn () => route('seller-invoicing.index'),
                'permissions' => ['seller-invoicing.index', 'seller-invoicing.view'],
            ]);

            if (\SparroWave\SellerInvoicing\Supports\TaxEngine::isGstReportEnabled()) {
                DashboardMenu::make()->registerItem([
                    'id' => 'sparrowave-seller-invoicing-gst-reports',
                    'priority' => 16,
                    'parent_id' => 'cms-plugins-marketplace',
                    'name' => 'plugins/seller-invoicing::seller-invoicing.gst_reports.menu_title',
                    'icon' => 'ti ti-file-analytics',
                    'url' => fn () => route('seller-invoicing.gst-reports.index'),
                    'permissions' => ['seller-invoicing.gst-reports', 'seller-invoicing.index'],
                ]);
            }
        });

        DashboardMenu::for('vendor')->beforeRetrieving(function (): void {
            DashboardMenu::make()->registerItem([
                'id' => 'marketplace.vendor.seller-invoicing',
                'priority' => 7,
                'name' => trans('plugins/seller-invoicing::seller-invoicing.library'),
                'url' => fn () => route('marketplace.vendor.seller-invoicing.index'),
                'icon' => 'ti ti-file-invoice',
            ]);

            if (\SparroWave\SellerInvoicing\Supports\TaxEngine::isGstReportEnabled()) {
                DashboardMenu::make()->registerItem([
                    'id' => 'marketplace.vendor.seller-invoicing-gst-reports',
                    'priority' => 8,
                    'name' => trans('plugins/seller-invoicing::seller-invoicing.gst_reports.menu_title'),
                    'url' => fn () => route('marketplace.vendor.seller-invoicing.gst-reports.index'),
                    'icon' => 'ti ti-file-analytics',
                ]);
            }
        });

        // Extend Marketplace Settings Form with Seller Invoicing fields
        if (class_exists(MarketplaceSettingForm::class)) {
            MarketplaceSettingForm::extend(function (MarketplaceSettingForm $form): void {
                MarketplaceSettingHookListener::extendForm($form);
            });
        }

        // Listen for save on Marketplace settings
        $this->app['events']->listen(\Illuminate\Routing\Events\RouteMatched::class, function (): void {
            $request = request();
            if ($request && ($request->isMethod('POST') || $request->isMethod('PUT'))) {
                if ($request->routeIs('marketplace.settings') || $request->routeIs('marketplace.settings.update') || $request->is('admin/marketplaces/settings*')) {
                    MarketplaceSettingHookListener::saveSettings($request);
                }
            }
        });

        // Register editable invoice template in Ecommerce Invoice Template Settings
        add_filter('ecommerce_invoice_templates', function (array $templates): array {
            $service = new GenerateSellerInvoiceService();

            return [
                ...$templates,
                'seller-invoice' => [
                    'label' => trans('plugins/seller-invoicing::seller-invoicing.invoice_template_label'),
                    'content' => fn () => $service->getContent(),
                    'variables' => fn () => $service->getVariables(),
                    'customized_path' => $service->getCustomizedTemplatePath(),
                    'preview' => fn () => $service->preview(),
                ],
            ];
        }, 999);

        // Admin Order Detail: Seller Fee Invoice Card & Buttons (only for finished/fulfilled orders)
        add_filter('ecommerce_admin_order_extra_info', function (?string $html, Order $order): string {
            if (! auth()->check() || ! auth()->user()?->hasPermission('seller-invoicing.view') || ! $order->store_id || ! $order->is_finished) {
                return $html ?? '';
            }

            $document = Document::query()->where('order_id', $order->getKey())->first();
            $canManage = auth()->user()?->hasPermission('seller-invoicing.manage');

            return ($html ?? '') . view('plugins/seller-invoicing::order-link', compact('order', 'document', 'canManage'))->render();
        }, 130, 2);

        // Vendor Order Detail: Seller Fee Invoice Action Buttons
        add_filter('ecommerce_order_detail_top', function (?string $html, Order $order): string {
            if (! auth('customer')->check()) {
                return $html ?? '';
            }

            $customerId = (int) auth('customer')->id();
            $isStoreOwner = (int) $order->store?->customer_id === $customerId;

            $document = Document::query()->where('order_id', $order->getKey())->first();
            $isDocumentSeller = $document && (int) $document->seller_id === $customerId;

            if ($isStoreOwner || $isDocumentSeller) {
                return ($html ?? '') . view('plugins/seller-invoicing::vendor-order-link', compact('order', 'document'))->render();
            }

            return $html ?? '';
        }, 130, 2);

        // Auto-generate invoice when order status changes to COMPLETED
        add_action(ACTION_AFTER_ORDER_STATUS_COMPLETED_ECOMMERCE, function (Order $order): void {
            if (! setting('seller_invoicing_enabled', true)) {
                return;
            }

            try {
                app(InvoiceGeneratorService::class)->generateForOrder($order);
            } catch (Throwable $exception) {
                report($exception);
            }
        }, 35);

        // Auto-cancel seller invoice when order status is CANCELED
        if (class_exists(\Botble\Ecommerce\Events\OrderCancelledEvent::class)) {
            \Illuminate\Support\Facades\Event::listen(\Botble\Ecommerce\Events\OrderCancelledEvent::class, function (\Botble\Ecommerce\Events\OrderCancelledEvent $event): void {
                try {
                    if ($event->order) {
                        \SparroWave\SellerInvoicing\Models\Document::query()
                            ->where('order_id', $event->order->getKey())
                            ->update(['status' => 'canceled']);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            });
        }

        if (class_exists(Order::class)) {
            Order::saved(function (Order $order): void {
                if (in_array($order->status, [\Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED, 'canceled'])) {
                    \SparroWave\SellerInvoicing\Models\Document::query()
                        ->where('order_id', $order->getKey())
                        ->where('status', '!=', 'canceled')
                        ->update(['status' => 'canceled']);
                }
            });
        }

        // Inject Date Range Filter Bar before SellerInvoiceTable, VendorSellerInvoiceTable, and OrderTable
        add_filter(BASE_FILTER_TABLE_BEFORE_RENDER, function (?string $html, $table): ?string {
            $isSellerInvoiceTable = $table instanceof \SparroWave\SellerInvoicing\Tables\SellerInvoiceTable;
            $isVendorInvoiceTable = $table instanceof \SparroWave\SellerInvoicing\Tables\VendorSellerInvoiceTable;
            $isOrderTable = is_a($table, 'Botble\Ecommerce\Tables\OrderTable');

            if ($isSellerInvoiceTable || $isVendorInvoiceTable || $isOrderTable) {
                $filterHtml = view('plugins/seller-invoicing::partials.date-range-filter', [
                    'table' => $table,
                    'isOrderTable' => $isOrderTable,
                    'isVendor' => $isVendorInvoiceTable,
                ])->render();

                return ($html ?? '') . $filterHtml;
            }

            return $html;
        }, 120, 2);

        // Apply Date Range Filter on OrderTable, SellerInvoiceTable, and VendorSellerInvoiceTable
        add_filter(BASE_FILTER_TABLE_QUERY, function ($query, $table) {
            $request = request();
            $dateFrom = $request->input('date_from') ?: $request->input('order_date_from');
            $dateTo = $request->input('date_to') ?: $request->input('order_date_to');
            $dateType = $request->input('date_type', 'order_date');

            if (! $dateFrom && ! $dateTo) {
                return $query;
            }

            if (is_a($table, 'Botble\Ecommerce\Tables\OrderTable')) {
                if ($dateFrom) {
                    $query->whereDate('ec_orders.created_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $query->whereDate('ec_orders.created_at', '<=', $dateTo);
                }
            } elseif ($table instanceof \SparroWave\SellerInvoicing\Tables\SellerInvoiceTable
                || $table instanceof \SparroWave\SellerInvoicing\Tables\VendorSellerInvoiceTable) {
                $column = $dateType === 'issued_at' ? 'si_documents.issued_at' : 'ec_orders.created_at';
                if ($dateFrom) {
                    $query->whereDate($column, '>=', $dateFrom);
                }
                if ($dateTo) {
                    $query->whereDate($column, '<=', $dateTo);
                }
            }

            return $query;
        }, 120, 2);

        // Register Order Date filter in OrderTable's filter drawer
        add_filter('ecommerce_order_table_filters', function (array $filters, $table): array {
            $filters['order_date'] = [
                'title' => trans('plugins/seller-invoicing::seller-invoicing.order_date'),
                'type' => 'datePicker',
            ];

            return $filters;
        }, 120, 2);

        add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function (?string $html): ?string {
            if (! \Botble\Base\Facades\AdminHelper::isInAdmin() && ! request()->is('vendor/*')) {
                return $html;
            }

            $style = '<style>
#sparro-wave-seller-invoicing-tables-seller-invoice-table_wrapper table.dataTable>tbody>tr.selected .text-primary,
#sparro-wave-seller-invoicing-tables-seller-invoice-table_wrapper table.dataTable>tbody>tr.selected .text-success,
#sparro-wave-seller-invoicing-tables-vendor-seller-invoice-table_wrapper table.dataTable>tbody>tr.selected .text-primary,
#sparro-wave-seller-invoicing-tables-vendor-seller-invoice-table_wrapper table.dataTable>tbody>tr.selected .text-success { color: #fff !important; }
#sparro-wave-seller-invoicing-tables-seller-invoice-table_wrapper table.dataTable>tbody>tr.selected .border-success,
#sparro-wave-seller-invoicing-tables-vendor-seller-invoice-table_wrapper table.dataTable>tbody>tr.selected .border-success { border-color: #fff !important; }
</style>';

            return ($html ?? '') . $style;
        }, 130);
    }
}
