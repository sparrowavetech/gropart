<table style="width:100%;border-collapse:collapse;table-layout:fixed;margin:4px 0;font-size:10px;page-break-inside:avoid">
    <tbody>
        <tr>
            <td colspan="2" style="padding:2px 0;font-weight:bold">{{ trans('plugins/advanced-cod::advanced-cod.invoice_payment_summary') }} ({{ $s['currency'] }})</td>
        </tr>
        @foreach ([
            'order_total_with_fee' => $s['total'],
            'invoice_total_received' => $s['received'],
            'customer_outstanding' => $s['outstanding'],
        ] as $key => $value)
            <tr>
                <td style="width:65%;padding:2px 4px 2px 0;vertical-align:top">{{ trans('plugins/advanced-cod::advanced-cod.' . $key) }}</td>
                <td style="width:35%;padding:2px 0;text-align:right;white-space:nowrap;vertical-align:top">{{ number_format($value / 100, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
