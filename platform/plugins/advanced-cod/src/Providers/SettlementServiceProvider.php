<?php
namespace SparroWave\AdvancedCod\Providers;

use Botble\Base\Facades\AdminHelper;
use Botble\Ecommerce\Models\{Order,Invoice};
use Botble\Payment\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use SparroWave\AdvancedCod\Supports\Settlement;

class SettlementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(\Botble\Ecommerce\Listeners\UpdateInvoiceWhenOrderCompleted::class,
            \SparroWave\AdvancedCod\Listeners\UpdateInvoiceWhenOrderCompleted::class);
    }

    public function boot(): void
    {
        $this->app->booted(function () {
            add_filter('ecommerce_admin_order_extra_info', function ($html,$order) {
                if (!Settlement::applies($order)) return $html;
                return $html.'<tr><td colspan="2">'.view('plugins/advanced-cod::settlement.summary',[
                    'order'=>$order,'s'=>Settlement::summary($order),'internal'=>AdminHelper::isInAdmin(),
                ])->render().'</td></tr>';
            },200,2);
            add_filter('ecommerce_admin_invoice_after_subtotal', function ($html,$invoice) {
                $order=$this->order($invoice);
                if (!$order) return $html;
                return $html.'<tr><td colspan="6">'.view('plugins/advanced-cod::settlement.summary',[
                    'order'=>$order,'s'=>Settlement::summary($order),'internal'=>true,
                ])->render().'</td></tr>';
            },200,2);
            add_filter('invoice_payment_info_filter', function ($html,$invoice) {
                $order=$this->order($invoice);
                if (!$order) return $html;
                return $html.view('plugins/advanced-cod::settlement.summary',[
                    'order'=>$order,'s'=>Settlement::summary($order),'internal'=>false,
                ])->render();
            },200,2);
            add_filter('ecommerce_invoice_variables', function ($data,$invoice) {
                $order=$this->order($invoice);
                if (!$order) return $data;
                $s=Settlement::summary($order);
                $data['payment_status']=$s['outstanding']===0 && !$s['review'] && !$s['cancelled'] ? 'completed' : 'pending';
                $data['payment_status_label']=$s['label'];
                $data['payment_method']=($s['required']===0?'Cash on delivery':($s['manual']>0?'Manual advance + Cash on delivery':'Online advance + Cash on delivery'));
                return $data;
            },999,2);
            Invoice::saving(fn ($invoice)=>Settlement::invoiceState($invoice));
            \Botble\Ecommerce\Models\Shipment::saving(function($shipment){
                $order=Order::find($shipment->order_id);
                if(!Settlement::applies($order))return;
                $s=Settlement::summary($order);
                $paid=$s['outstanding']===0 && !$s['review'] && !$s['cancelled'];
                if($shipment->exists && $shipment->isDirty('cod_status') && (string)$shipment->cod_status==='completed' && !$paid)
                    throw \Illuminate\Validation\ValidationException::withMessages(['cod_status'=>'Record actual collection through Manage COD collection / remittance first.']);
                $shipment->cod_amount=($s['cod_pending']??$s['planned_cod'])/100;
                $shipment->cod_status=$paid?'completed':'pending';
            });
            Payment::saved(function ($payment) { $this->syncOrder((int)$payment->order_id); });
            Order::updated(function ($order) {
                if ($order->wasChanged('status')) $this->syncOrder((int)$order->id);
            });
            add_filter('ecommerce_cod_collection_override', function ($amount,$order) {
                if (!Settlement::applies($order)) return $amount;
                $s=Settlement::summary($order);
                if ($s['review'] || $s['cancelled'] || $s['advance_received']<$s['required']) throw new \RuntimeException('COD settlement requires reconciliation before shipping.');
                return $s['outstanding']/100;
            },20,2);
        });
    }

    private function order(Invoice $invoice): ?Order
    {
        if ($invoice->reference_type !== Order::class) return null;
        $order=Order::find($invoice->reference_id);
        return Settlement::applies($order)?$order:null;
    }

    private function syncOrder(int $id): void
    {
        DB::transaction(function () use ($id) {
            $order=Order::whereKey($id)->lockForUpdate()->first();
            if ($order && Settlement::applies($order)) Settlement::sync($order);
        });
    }
}
