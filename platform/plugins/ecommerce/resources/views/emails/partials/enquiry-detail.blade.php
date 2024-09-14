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
                <img src="{{ RvMedia::getImageUrl($enquiry->product->product_image, 'thumb') }}"  alt="{{ $enquiry->product->name }}"  class=" bb-rounded" width="50" alt="" />
            </td>
            <td>
                {{ $enquiry->product->name }}
            </td>
        </tr>
    </table><br>
</div>