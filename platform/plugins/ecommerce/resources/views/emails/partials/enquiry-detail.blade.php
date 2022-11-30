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
           
            <tr>
                <td>
                    <img src="{{ RvMedia::getImageUrl($enquiry->product->product_image, 'thumb') }}" alt="{{ $enquiry->product->name }}" width="50">
                </td>
                <td>
                    {{ $enquiry->product->name }}
                </td>

                <td>
                    {{ format_price($enquiry->product->price) }}
            </tr>
    </table><br>
</div>

