@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
<div class="card"><div class="card-body">
    <h3>Advanced COD — {{ $order->code }}</h3>
    <p>Confirming places the order. It does not mark delivery or the full payment as completed.</p>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    @if($order->is_finished)<div class="alert alert-info">This order is already placed.</div>@endif
    @if(!$p['flags']['cod'])<div class="alert alert-warning">COD is not enabled for every product. Confirmation is blocked.</div>@endif
    @php($receipts=\SparroWave\AdvancedCod\Supports\Settlement::applies($order)?\SparroWave\AdvancedCod\Supports\Settlement::summary($order):null)
    <table class="table"><tbody>
    @foreach(['Order total (including COD fee)'=>$p['total'],'Included COD collection fee'=>$p['fee'],'Required advance'=>$p['advance'],'Remaining after advance'=>$p['remaining'],'Actual received (net)'=>($receipts['received']??0)/100,'Customer outstanding'=>max(0,$p['total']-($receipts['received']??0)/100)] as $label=>$amount)
    <tr><th>{{ $label }}</th><td @if($label==='Required advance') id="cod-preview-advance" @elseif($label==='Remaining after advance') id="cod-preview-remaining" @endif>{{ format_price($amount) }}</td></tr>
    @endforeach
    </tbody></table>
    <p>{{ $p['frozen']?'Existing payment totals are frozen.':'This preview replaces the old payment fee with the current COD fee; shipping, tax and discounts stay as saved.' }}</p>
    <div class="alert alert-info"><strong>Advance pending?</strong> Keep this order incomplete and share the <a href="{{ route('public.checkout.recover',$order->token) }}" target="_blank" rel="noopener">customer checkout / retry link</a>. The customer should select COD and verify the checkout total. No receipt is recorded by opening this link.</div>
    @if($p['locked'])<p>A gateway attempt/receipt exists. Manual receipt and full-COD conversion are blocked until reconciled; a verified matching advance can still be used.</p>@endif
    @if($p['retry_required'])<div class="alert alert-warning">These are historical payment-attempt amounts, not a new COD quote. The previous payment attempts have inconsistent totals. Use the checkout / retry link above for a fresh fee and advance calculation, or reconcile any received payment before confirming.</div>@endif
    @if(!$order->is_finished && !$p['retry_required'])
    <form action="{{ route('advanced-cod.confirm.store',$order->id) }}" method="POST">
        @csrf
        <input type="hidden" name="fingerprint" value="{{ $p['fingerprint'] }}">
        <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key',(string)\Illuminate\Support\Str::uuid()) }}">
        <div class="row"><div class="col-md-6 mb-3"><label for="cod-confirm-mode" class="form-label">Confirmation method</label><select name="mode" id="cod-confirm-mode" class="form-select">
            <option value="verified">Use existing verified online advance</option>
            @if(!$p['locked'] && auth()->user()->hasPermission('advanced-cod.manual-receipt'))<option value="manual" @selected(old('mode')==='manual')>Advance manually received (cash / bank / UPI)</option>@endif
            @if(!$p['locked'] && !$p['frozen'] && get_payment_setting('admin_full_cod_override','cod',0) && auth()->user()->hasPermission('advanced-cod.override'))<option value="full_cod" @selected(old('mode')==='full_cod')>Full COD without advance — approved override</option>@endif
        </select></div></div>
        <fieldset id="cod-manual-fields"><div class="row">
            <div class="col-md-6 mb-3"><label class="form-label" for="cod-manual-amount">Actual advance received</label><input class="form-control" type="number" step="0.01" min="0.01" name="amount" id="cod-manual-amount" value="{{ old('amount') }}"></div>
            <div class="col-md-6 mb-3"><label class="form-label" for="cod-manual-method">Receipt method</label><select class="form-select" name="method" id="cod-manual-method"><option value="cash">Cash</option><option value="bank" @selected(old('method')==='bank')>Bank transfer</option><option value="upi" @selected(old('method')==='upi')>UPI received outside checkout</option></select></div>
            <div class="col-md-6 mb-3"><label class="form-label" for="cod-manual-reference">Receipt / transaction reference</label><input class="form-control" name="reference" id="cod-manual-reference" maxlength="120" value="{{ old('reference') }}"></div>
            <div class="col-md-6 mb-3"><label class="form-label" for="cod-manual-time">Received at ({{ config('app.timezone') }})</label><input class="form-control" type="datetime-local" name="occurred_at" id="cod-manual-time" value="{{ old('occurred_at') }}"></div>
        </div></fieldset>
        <div class="mb-3"><label class="form-label" for="cod-confirm-note">Evidence / reason</label><textarea class="form-control" name="note" id="cod-confirm-note" required maxlength="1000">{{ old('note') }}</textarea></div>
        <button class="btn btn-primary" type="submit" @disabled(!$p['flags']['cod'])>Confirm &amp; place order</button>
    </form>
    <script>document.addEventListener('DOMContentLoaded',function(){const m=document.getElementById('cod-confirm-mode'),f=document.getElementById('cod-manual-fields');function update(){document.getElementById('cod-preview-advance').textContent=m.value==='full_cod'?@json(format_price(0)):@json(format_price($p['advance']));document.getElementById('cod-preview-remaining').textContent=m.value==='full_cod'?@json(format_price($p['total'])):@json(format_price($p['remaining']));f.hidden=m.value!=='manual';f.disabled=f.hidden;for(const e of f.querySelectorAll('input,select'))e.required=!f.hidden;}m.addEventListener('change',update);update();});</script>
    @endif
</div></div>
@endsection
