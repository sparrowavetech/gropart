@if(auth()->user()?->hasPermission('advanced-cod.manual-confirm') && auth()->user()?->hasPermission('orders.edit'))
<tr><td colspan="2" class="text-start"><a class="btn btn-primary my-2" href="{{ route('advanced-cod.confirm.show',$order->id) }}">Review Advanced COD &amp; place order</a></td></tr>
@if(!$order->is_finished)
<script>
document.addEventListener('DOMContentLoaded',function(){
    const modal=document.getElementById('mark-order-as-completed-modal');if(!modal)return;
    const method=modal.querySelector('[name="payment_method"]');
    const submit=modal.querySelector('[data-bb-toggle="confirm-mark-as-completed-button"]');
    if(!method||!submit)return;
    const link=document.createElement('a');link.className='btn btn-primary ms-auto';link.href=@json(route('advanced-cod.confirm.show',$order->id));link.textContent='Review Advanced COD & place order';submit.after(link);
    const manual=method.querySelector('option[value="advanced_cod_manual"]');if(manual)manual.remove();
    const frozen=true;
    function update(){const active=method.value==='cod'||frozen;submit.hidden=active;link.hidden=!active;
        for(const name of ['payment_status','transaction_id']){const el=modal.querySelector('[name="'+name+'"]');if(el){const group=el.closest('.mb-3,.form-group');if(group)group.hidden=active;el.disabled=active;}}
    }method.addEventListener('change',update);modal.addEventListener('shown.bs.modal',update);update();
});
</script>
@endif

@endif
