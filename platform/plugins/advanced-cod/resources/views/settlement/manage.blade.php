@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
<div class="card"><div class="card-body">
    <a href="{{ route('orders.edit',$order->id) }}">Back to order {{ $order->code }}</a>
    @include('plugins/advanced-cod::settlement.summary',['internal'=>false])
    <p>Courier cash awaiting remittance: <strong>{{ format_price($s['remittance_pending']/100) }}</strong> · Remitted: {{ format_price($s['remitted']/100) }} · Deductions: {{ format_price($s['deductions']/100) }}</p>
    <p>Record only money already collected or received. Shipment delivery alone does not confirm cash collection or courier remittance. Existing entries cannot be edited; use a reversal to correct mistakes.</p>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    @if(session('success_msg'))<div class="alert alert-success">{{ session('success_msg') }}</div>@endif
    <form method="POST" action="{{ route('advanced-cod.settlement.store',$order->id) }}">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key',(string)\Illuminate\Support\Str::uuid()) }}">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="cod-kind">Operation</label><select class="form-select" name="kind" id="cod-kind">
                @foreach(['collection'=>'Customer cash collected','remittance'=>'Courier remittance received','refund'=>'Cash refunded to customer','reversal'=>'Reverse an entry'] as $key=>$label)<option value="{{ $key }}" @selected(old('kind')===$key)>{{ $label }}</option>@endforeach
            </select></div>
            <div class="col-md-6"><label class="form-label" for="cod-source">Cash collected by</label><select class="form-select" name="source" id="cod-source"><option value="courier">Courier</option><option value="direct" @selected(old('source')==='direct')>Merchant directly</option></select><small>Used for collection only. Remittances come from courier; cash refunds are recorded as merchant refunds.</small></div>
            <div class="col-md-6"><label class="form-label" for="cod-amount">Amount ({{ $s['currency'] }})</label><input class="form-control" id="cod-amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount','0.00') }}" required><small>For remittance, enter the net money received. For reversal, enter 0; the original amount is used.</small></div>
            <div class="col-md-6"><label class="form-label" for="cod-fee">Courier deductions (remittance only)</label><input class="form-control" id="cod-fee" name="fee" type="number" min="0" step="0.01" value="{{ old('fee','0.00') }}" required><small>Net received + deductions clears courier receivable. This does not add to the customer's fee or total.</small></div>
            <div class="col-md-6"><label class="form-label" for="cod-reference">Unique receipt / settlement reference</label><input class="form-control" id="cod-reference" name="reference" maxlength="120" value="{{ old('reference') }}" required></div>
            <div class="col-md-6"><label class="form-label" for="cod-time">Received / occurred at</label><input class="form-control" id="cod-time" name="occurred_at" type="datetime-local" value="{{ old('occurred_at',now()->format('Y-m-d\TH:i')) }}" required><small>{{ config('app.timezone') }}</small></div>
            <div class="col-md-6"><label class="form-label" for="cod-reversal">Original entry ID (reversal only)</label><input class="form-control" id="cod-reversal" name="reversal_of" type="number" min="1" value="{{ old('reversal_of') }}"></div>
            <div class="col-12"><label class="form-label" for="cod-note">Evidence / note</label><textarea class="form-control" id="cod-note" name="note" maxlength="2000" required>{{ old('note') }}</textarea></div>
            <div class="col-12"><button class="btn btn-primary" type="submit">Record settlement</button></div>
        </div>
    </form>
    <h3 class="mt-4">Settlement history</h3>
    <div class="table-responsive"><table class="table"><thead><tr><th>ID / time</th><th>Operation</th><th>Collected by</th><th>Amount</th><th>Deductions</th><th>Reference / evidence</th><th>Recorded by</th></tr></thead><tbody>
        @forelse($s['entries'] as $entry)<tr>
            <td>#{{ $entry->id }}<br>{{ $entry->occurred_at }}</td><td>{{ $entry->kind }}@if($entry->reversal_of) of #{{ $entry->reversal_of }}@endif @if(in_array($entry->id,$s['reversed'])) (reversed) @endif</td>
            <td>{{ $entry->source }}</td><td>{{ format_price($entry->amount_paise/100) }}</td><td>{{ format_price($entry->fee_paise/100) }}</td><td>{{ $entry->reference }}<br>{{ $entry->note }}</td><td>User #{{ $entry->user_id }}</td>
        </tr>@empty<tr><td colspan="7">No cash collection or remittance recorded.</td></tr>@endforelse
    </tbody></table></div>
</div></div>
@endsection
