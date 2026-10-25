<?php

namespace SparroWave\Shipmozo\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\Marketplace\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use SparroWave\Shipmozo\Shipmozo;

class ShipmozoWarehouseController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/marketplace::marketplace.name'))
            ->add(trans('plugins/shipmozo::shipmozo.warehouses.page_title'), route('marketplace.shipmozo-warehouses.index'));
    }

    public function __construct(protected Shipmozo $shipmozo)
    {
    }

    public function index(Request $request)
    {
        abort_unless(is_plugin_active('marketplace'), 404);

        $this->pageTitle(trans('plugins/shipmozo::shipmozo.warehouses.page_title'));

        $query = Store::query()->with('customer');

        $statusFilter = $request->input('status', 'all');
        if ($statusFilter === 'mapped') {
            $query->whereNotNull('warehouse_id')->where('warehouse_id', '!=', '');
        } elseif ($statusFilter === 'unmapped') {
            $query->where(function ($q) {
                $q->whereNull('warehouse_id')->orWhere('warehouse_id', '');
            });
        }

        $search = trim((string) $request->input('search'));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('warehouse_id', 'like', "%{$search}%")
                    ->orWhere('zip_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $stores = $query->paginate(20);

        // Fetch live warehouses list from ShipMozo API
        $apiWarehouses = [];
        try {
            $res = $this->shipmozo->getWarehouses();
            $data = Arr::get($res, 'data', []);
            foreach ($data as $w) {
                if (isset($w['id'])) {
                    $apiWarehouses[(string) $w['id']] = $w;
                }
            }
        } catch (\Throwable $e) {
            $this->shipmozo->logError('Failed to load warehouses from API in controller', ['message' => $e->getMessage()]);
        }

        $totalStores = Store::count();
        $mappedStores = Store::whereNotNull('warehouse_id')->where('warehouse_id', '!=', '')->count();
        $unmappedStores = $totalStores - $mappedStores;

        return view('plugins/shipmozo::warehouses.index', compact(
            'stores',
            'apiWarehouses',
            'totalStores',
            'mappedStores',
            'unmappedStores',
            'statusFilter',
            'search'
        ));
    }

    public function edit(int|string $storeId, BaseHttpResponse $response)
    {
        abort_unless(is_plugin_active('marketplace'), 404);

        $store = Store::findOrFail($storeId);

        $this->pageTitle(trans('plugins/shipmozo::shipmozo.warehouses.edit_warehouse_title') . ': ' . $store->name);
        $this->breadcrumb()->add($store->name, route('marketplace.shipmozo-warehouses.edit', $store->id));

        $apiWarehouses = [];
        try {
            $res = $this->shipmozo->getWarehouses();
            $apiWarehouses = Arr::get($res, 'data', []);
        } catch (\Throwable $e) {
        }

        $currentWarehouse = null;
        if ($store->warehouse_id) {
            $currentWarehouse = Arr::first($apiWarehouses, fn ($w) => (string) ($w['id'] ?? '') === (string) $store->warehouse_id);
        }

        return view('plugins/shipmozo::warehouses.edit', compact('store', 'apiWarehouses', 'currentWarehouse'));
    }

    public function update(int|string $storeId, Request $request, BaseHttpResponse $response)
    {
        abort_unless(is_plugin_active('marketplace'), 404);

        $store = Store::findOrFail($storeId);

        // Two modes: 1) Link existing warehouse ID, 2) Create/Update warehouse details via API
        $mode = $request->input('action_mode', 'details');

        if ($mode === 'link') {
            $warehouseId = $request->input('warehouse_id');
            $store->warehouse_id = $warehouseId ?: null;
            $store->save();

            return $response
                ->setNextUrl(route('marketplace.shipmozo-warehouses.index'))
                ->setMessage(trans('plugins/shipmozo::shipmozo.warehouses.linked_success'));
        }

        $validated = $request->validate([
            'address_title' => ['required', 'string', 'max:120'],
            'contact_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['required', 'string', 'max:255'],
            'pincode' => ['required', 'numeric', 'digits:6'],
        ]);

        try {
            $payload = [
                'address_title' => $validated['address_title'],
                'contact_name' => $validated['contact_name'],
                'phone' => $validated['phone'],
                'contact_email' => $validated['email'] ?: $store->email,
                'address' => $validated['address'],
                'pincode' => $validated['pincode'],
            ];

            $result = $this->shipmozo->createWarehouse($payload);

            if (Arr::get($result, 'result') == 1) {
                $newWarehouseId = Arr::get($result, 'data.warehouse_id', Arr::get($result, 'id'));
                if ($newWarehouseId) {
                    $store->warehouse_id = $newWarehouseId;
                    $store->save();
                }

                return $response
                    ->setNextUrl(route('marketplace.shipmozo-warehouses.index'))
                    ->setMessage(trans('plugins/shipmozo::shipmozo.warehouses.updated_success'));
            }

            $errorMsg = Arr::get($result, 'message') ?: Arr::get($result, 'data.error', trans('plugins/shipmozo::shipmozo.warehouse_created_failed'));

            return $response
                ->setError()
                ->setMessage($errorMsg);
        } catch (\Throwable $e) {
            return $response
                ->setError()
                ->setMessage($e->getMessage());
        }
    }

    public function destroy(int|string $storeId, BaseHttpResponse $response)
    {
        abort_unless(is_plugin_active('marketplace'), 404);

        $store = Store::findOrFail($storeId);

        $store->warehouse_id = null;
        $store->save();

        return $response
            ->setNextUrl(route('marketplace.shipmozo-warehouses.index'))
            ->setMessage(trans('plugins/shipmozo::shipmozo.warehouses.unlinked_success'));
    }

    public function syncAll(BaseHttpResponse $response)
    {
        abort_unless(is_plugin_active('marketplace'), 404);

        $unmappedStores = Store::whereNull('warehouse_id')
            ->orWhere('warehouse_id', '')
            ->get();

        $synced = 0;
        foreach ($unmappedStores as $store) {
            if ($store->zip_code && preg_match('/^[1-9][0-9]{5}$/', (string) $store->zip_code) && $store->address) {
                try {
                    $res = $this->shipmozo->syncWarehouse($store);
                    $wId = Arr::get($res, 'data.warehouse_id', Arr::get($res, 'id'));
                    if ($wId) {
                        $store->warehouse_id = $wId;
                        $store->saveQuietly();
                        $synced++;
                    }
                } catch (\Throwable) {
                }
            }
        }

        return $response
            ->setNextUrl(route('marketplace.shipmozo-warehouses.index'))
            ->setMessage(trans('plugins/shipmozo::shipmozo.warehouses.sync_all_success', ['count' => $synced]));
    }
}
