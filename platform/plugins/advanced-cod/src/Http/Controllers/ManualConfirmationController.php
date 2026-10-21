<?php

namespace SparroWave\AdvancedCod\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Order;
use Illuminate\Http\Request;
use SparroWave\AdvancedCod\Supports\ManualConfirmation;

class ManualConfirmationController extends BaseController
{
    private function authorizeConfirmation(): void
    {
        abort_unless(auth()->user()?->hasPermission('orders.edit') && auth()->user()?->hasPermission('advanced-cod.manual-confirm'), 403);
    }

    public function show(Order $order)
    {
        $this->authorizeConfirmation();
        abort_unless(ManualConfirmation::available($order), 404);

        $this->pageTitle(trans('plugins/advanced-cod::advanced-cod.manual_confirmation_title') . ' — ' . $order->code);

        $this->breadcrumb()
            ->add(trans('plugins/ecommerce::order.menu_name'), route('orders.index'))
            ->add(
                $order->code,
                $order->is_finished ? route('orders.edit', $order->id) : route('orders.view-incomplete-order', $order->id)
            )
            ->add(trans('plugins/advanced-cod::advanced-cod.review_and_place_order'));

        return view('plugins/advanced-cod::manual.confirm', [
            'order' => $order,
            'p' => ManualConfirmation::preview($order),
        ]);
    }

    public function store(Order $order, Request $request)
    {
        $this->authorizeConfirmation();

        $data = $request->validate([
            'mode' => 'required|in:verified,manual,full_cod',
            'fingerprint' => 'required|string|size:64',
            'idempotency_key' => 'required|uuid',
            'amount' => ['required_if:mode,manual', 'nullable', 'numeric', 'min:0', 'max:999999999', 'regex:/^\d+(\.\d{1,2})?$/'],
            'method' => 'required_if:mode,manual|nullable|in:cash,bank,upi',
            'reference' => 'required_if:mode,manual|nullable|string|max:120',
            'occurred_at' => 'required_if:mode,manual|nullable|date|before_or_equal:now',
            'note' => 'required|string|max:1000',
        ]);

        $order = ManualConfirmation::confirm($order, $data, auth()->user());

        return redirect()->route('orders.edit', $order->id)->with('success_msg', 'Order placed successfully with Advanced COD.');
    }

    public function settings()
    {
        abort_unless(auth()->user()?->hasPermission('orders.create') || auth()->user()?->hasPermission('orders.edit'), 403);

        return response()->json([
            'percentage' => (int) get_payment_setting('prepayment_percentage', 'cod', 30),
            'currency_symbol' => get_application_currency()->symbol,
            'currency_title' => get_application_currency()->title,
            'admin_full_cod_override' => (bool) get_payment_setting('admin_full_cod_override', 'cod', 0),
        ]);
    }
}
