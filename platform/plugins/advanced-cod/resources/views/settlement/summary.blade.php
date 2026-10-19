<div class="advanced-cod-summary" style="width:100%;float:none;box-sizing:border-box;page-break-inside:avoid;border:1px solid #ccd7d0;padding:12px;margin:12px 0;font-size:13px;text-align:left">
    <small>{{ $s['currency'] }}</small><br><strong>Advanced COD — {{ $s['label'] }}</strong>
    <table style="width:100%;margin-top:8px">
        @foreach ([
            'Order total'=>$s['total'],
            'COD cash-collection charge (included in total)'=>$s['fee'],
            'Required advance'=>$s['required'],
            'Online received (net of recorded refunds)'=>$s['online'],
            'Manual advance received'=>$s['manual'],
            'Cash collected from customer'=>$s['courier']+$s['direct'],
            'Cash refunded'=>$s['cashRefund'],
            'Total received (net)'=>$s['received'],
            'Customer outstanding'=>$s['outstanding'],
        ] as $label=>$value)
        <tr><td>{{ $label }}</td><td style="text-align:right;white-space:nowrap">{{ number_format($value/100,2) }}</td></tr>
        @endforeach
        <tr><td>{{ $s['cod_pending'] === null ? 'Planned COD after advance is paid' : 'COD collection pending' }}</td><td style="text-align:right">{{ number_format(($s['cod_pending'] ?? $s['planned_cod'])/100,2) }}</td></tr>
        @if ($internal ?? false)
            <tr><td>Courier cash awaiting remittance</td><td style="text-align:right">{{ number_format($s['remittance_pending']/100,2) }}</td></tr>
            <tr><td>Courier remittance received (net)</td><td style="text-align:right">{{ number_format($s['remitted']/100,2) }}</td></tr>
            <tr><td>Courier deductions recorded</td><td style="text-align:right">{{ number_format($s['deductions']/100,2) }}</td></tr>
        @endif
    </table>
    <small>Fee is already included, not an additional charge. A completed online transaction confirms only the amount received online.</small>
    @if ($s['review'] || $s['cancelled'])<p>Reconcile refunds/cancellation before requesting further payment.</p>@endif
    @if (($internal ?? false) && auth()->user()?->hasPermission('advanced-cod.settle') && auth()->user()?->hasPermission('orders.edit'))
        <p style="margin-bottom:0"><a href="{{ route('advanced-cod.settlement.show',$order->id) }}">Manage COD collection / remittance</a></p>
    @endif
</div>
