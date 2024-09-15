<div class="table">
    <table>
        <tr>
            <th style="text-align: left">
                {{ trans('plugins/ecommerce::products.product_image') }}
            </th>
            <th style="text-align: left">
                {{ trans('plugins/ecommerce::products.form.product') }}
            </th>

        </tr>
        <tr>
            <td>
                <a href="">
                    <img src="{{ RvMedia::getImageUrl($enquiry->product->images[0], 'thumb') }}" alt="{{ $enquiry->product->name }}" class=" bb-rounded" width="50" alt="" />
                </a>
            </td>
            <td>
                {{ $enquiry->product->name }}
            </td>
        </tr>
    </table><br>
</div>