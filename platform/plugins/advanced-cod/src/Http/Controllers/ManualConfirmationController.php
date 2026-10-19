<?php
namespace SparroWave\AdvancedCod\Http\Controllers;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Order;
use Illuminate\Http\Request;
use SparroWave\AdvancedCod\Supports\ManualConfirmation;
class ManualConfirmationController extends BaseController
{
    private function authorizeConfirmation(): void {abort_unless(auth()->user()?->hasPermission('orders.edit') && auth()->user()?->hasPermission('advanced-cod.manual-confirm'),403);}
    public function show(Order $order){
        $this->authorizeConfirmation();abort_unless(ManualConfirmation::available($order),404);$this->pageTitle('Confirm & place order — '.$order->code);
        return view('plugins/advanced-cod::manual.confirm',['order'=>$order,'p'=>ManualConfirmation::preview($order)]);
    }
    public function store(Order $order,Request $request){
        $this->authorizeConfirmation();
        $data=$request->validate(['mode'=>'required|in:verified,manual,full_cod','fingerprint'=>'required|string|size:64','idempotency_key'=>'required|uuid',
            'amount'=>['required_if:mode,manual','nullable','numeric','min:0','max:999999999','regex:/^\d+(\.\d{1,2})?$/'],
            'method'=>'required_if:mode,manual|nullable|in:cash,bank,upi','reference'=>'required_if:mode,manual|nullable|string|max:120',
            'occurred_at'=>'required_if:mode,manual|nullable|date|before_or_equal:now','note'=>'required|string|max:1000']);
        $order=ManualConfirmation::confirm($order,$data,auth()->user());
        return redirect()->route('orders.edit',$order->id)->with('success_msg','Order placed. Payment balance remains based on actual receipts.');
    }
}
