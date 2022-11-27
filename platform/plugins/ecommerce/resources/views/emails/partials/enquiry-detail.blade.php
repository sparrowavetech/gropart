<div class="table">
    <table>
        <tr>
            <th style="text-align: left">
                {{ trans('plugins/ecommerce::products.product_image') }}
            </th>
            <th style="text-align: left">
                {{ trans('plugins/ecommerce::products.form.product') }}
            </th>
            <th style="text-align: left">
                {{ trans('plugins/ecommerce::products.form.price') }}
            </th>
        </tr>
            @php
                $product = get_products([
                    'condition' => [
                        'ec_products.id'     => $enquiry->product_id,
                    ],
                    'take'      => 1,
                    'select'    => [
                        'ec_products.id',
                        'ec_products.name',
                        'ec_products.price',
                        'ec_products.sale_price',
                        'ec_products.sale_type',
                        'ec_products.start_date',
                        'ec_products.end_date',
                        'ec_products.sku',
                        'ec_products.is_variation',
                        'ec_products.status',
                        'ec_products.order',
                        'ec_products.created_at',
                        'ec_products.images',
                    ],
                ]);
            @endphp
            <tr>
                <td>
                    <img src="{{ RvMedia::getImageUrl($enquiry->product->product_image, 'thumb') }}" alt="{{ $enquiry->product->name }}" width="50">
                </td>
                <td>
                    {{ $enquiry->product->name }}
                    @if ($product)
                        <small>{{ $product->variation_attributes }}</small>
                    @endif
                </td>

                <td>
                    {{ format_price($enquiry->product->price) }}
            </tr>
    </table><br>
</div>

