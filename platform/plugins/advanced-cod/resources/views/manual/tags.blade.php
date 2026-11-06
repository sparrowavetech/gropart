@if(auth()->user()?->hasPermission('advanced-cod.manual-confirm') && auth()->user()?->hasPermission('orders.edit'))
@if(!$order->is_finished)
<script>
document.addEventListener('DOMContentLoaded',function(){
    const modal=document.getElementById('mark-order-as-completed-modal');if(!modal)return;
    const method=modal.querySelector('[name="payment_method"]');
    const submit=modal.querySelector('[data-bb-toggle="confirm-mark-as-completed-button"]');
    if(!method||!submit)return;
    const link=document.createElement('a');link.className='btn btn-primary ms-auto';link.href=@json(route('advanced-cod.confirm.show',$order->id));link.textContent=@json(trans('plugins/advanced-cod::advanced-cod.review_and_place_order'));submit.after(link);
    const hasIntent = @json((bool) \SparroWave\AdvancedCod\Supports\AdvancePayment::intent($order));
    function update(){
        const isAdvancedCod = method.value === 'advanced_cod_manual' || (hasIntent && method.value === 'cod');
        submit.hidden = isAdvancedCod;
        link.hidden = !isAdvancedCod;
        for(const name of ['payment_status','transaction_id']){
            const el = modal.querySelector('[name="'+name+'"]');
            if(el){
                const group = el.closest('.mb-3,.form-group');
                if(group) group.hidden = isAdvancedCod;
                el.disabled = isAdvancedCod;
            }
        }
    }
    method.addEventListener('change',update);modal.addEventListener('shown.bs.modal',update);update();
});
</script>
@endif

@endif
