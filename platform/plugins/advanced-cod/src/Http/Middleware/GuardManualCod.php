<?php
namespace SparroWave\AdvancedCod\Http\Middleware;
use Botble\Ecommerce\Models\Order;
use SparroWave\AdvancedCod\Supports\AdvancePayment;
use SparroWave\AdvancedCod\Supports\ManualConfirmation;
class GuardManualCod
{
    public function handle($request,\Closure $next){
        if($request->routeIs('orders.mark-as-completed')){
            $value=$request->route('order');$order=$value instanceof Order?$value:Order::find($value);
            $hasLockedIntent = $order && (bool) AdvancePayment::intent($order);
            if($request->input('payment_method') === 'advanced_cod_manual' || ($hasLockedIntent && $request->input('payment_method') === 'cod')){
                throw \Illuminate\Validation\ValidationException::withMessages(['payment_method'=>'Use Advanced COD confirmation ("Review and Place Order") to verify the advance and remaining balance.']);
            }
        }
        return $next($request);
    }
}
