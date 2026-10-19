<?php
namespace SparroWave\AdvancedCod\Http\Controllers;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Order;
use Illuminate\Http\Request;
use SparroWave\AdvancedCod\Supports\Settlement;
class SettlementController extends BaseController
{
    private function authorizeOrder(Order $order): void
    {
        abort_unless(auth()->user()?->hasPermission('orders.edit') && auth()->user()?->hasPermission('advanced-cod.settle'),403);
        abort_unless(Settlement::applies($order),404);
    }
    public function show(Order $order)
    {
        $this->authorizeOrder($order);
        $this->pageTitle('Advanced COD settlement — '.$order->code);
        return view('plugins/advanced-cod::settlement.manage',['order'=>$order,'s'=>Settlement::summary($order)]);
    }
    public function store(Order $order, Request $request)
    {
        $this->authorizeOrder($order);
        $data=$request->validate([
            'kind'=>'required|in:collection,remittance,refund,reversal',
            'source'=>'required|in:direct,courier',
            'amount'=>['required','numeric','min:0','max:999999999','regex:/^\d+(\.\d{1,2})?$/'],
            'fee'=>['required','numeric','min:0','max:999999999','regex:/^\d+(\.\d{1,2})?$/'],
            'reference'=>'required|string|max:120',
            'idempotency_key'=>'required|uuid',
            'reversal_of'=>'nullable|integer|min:1',
            'note'=>'required|string|max:2000',
            'occurred_at'=>'required|date|before_or_equal:now',
        ]);
        Settlement::record($order,$data,(int)auth()->id());
        return redirect()->route('advanced-cod.settlement.show',$order->id)->with('success_msg','Settlement recorded.');
    }
}
