<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ 'plugins/ecommerce::order.invoice_for_order'|trans }} - {{ site_title }} - {{ invoice.reference.code }}</title>
    {% if settings.using_custom_font_for_invoice and settings.custom_font_family %}
    <link href="https://fonts.googleapis.com/css2?family={{ settings.custom_font_family | urlencode }}:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    {% endif %}
    <style>
        html, body { font-family: '{{ settings.font_family }}', Arial, sans-serif !important; font-size: .875em; margin: 0px;
               padding: 10px; }

        .text-left { text-align: left; } .text-right { text-align: right; }

        .mt-0 { margin: 0; } .mb-0 { margin: 0; } .m-0 { margin: 0; } .p-0 { padding: 0; } .right { text-align: right; }

        .large { font-size: 14px; } small { font-size: 8px; } p { margin: 0 0 5px; }

        .bold, strong, b, .total, .stamp { font-weight: 700; } .setionheading { text-transform: uppercase; font-size: 14px; }

        .mt-10 { margin-top: 10px; } .mt-20 { margin-top: 20px; } .mt-30 { margin-top: 30px; }

        .mb-10 { margin-bottom: 10px; } .mb-20 { margin-bottom: 20px; } .mb-30 { margin-bottom: 30px; }

        .invoice-container { max-width: 800px; margin: auto; border: 1px solid #ccc; padding: 20px; }

        .invoice-header { text-align: center; font-size: 16px; margin-bottom: 0px; }

        .invoice-details, .invoice-header { margin-bottom: 10px; min-width: 100%; }

        .invoice-details div, .invoice-header div { box-sizing: border-box; width: 48%; }

		.invoice-details p, .invoice-details p span, .invoice-details ul li span { font-size: 14px !important; }

        .invoice-details div:nth-child(odd), .invoice-header div:nth-child(odd) { float: left; margin-right: 15px; }

        .invoice-details div:nth-child(even), .invoice-header div:nth-child(even) { float: right; }

        .invoice-details::after, .invoice-header:after { content: ""; display: table; clear: both; }

        .item-table th.product-name { width: 30%; }

        .item-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }

        .item-table th, .item-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }

        .item-table tfoot th { font-size: .875em; }

        .item-table th:first-child, .item-table td:first-child { text-align: left; }

        .item-table th:last-child, .item-table td:last-child { text-align: right; }

        .item-table th { background-color: #f2f2f2; } .text-right { text-align: right !important; }

        .orderId { border: 1px dashed #aaa; padding: 5px 5px; margin-bottom: 10px; background-color: #f2f2f2; }

        .total { color: #00A650; font-weight : 700; }

        .invoice-info-container { font-size: .875em; } .invoice-info-container td { padding: 4px 0; }

        .stamp { border: 2px solid #555; color: #555; font-size: 14px; line-height: 1;  opacity: .5; padding: .3rem .75rem; text-transform: uppercase; display: inline-block; transform: rotate(-14deg); position: fixed; bottom: 30%; left: 35%; }

        .is-failed { border-color: #d23; color: #d23; }

        .is-completed { border-color: #0a9928; color: #0a9928; }

        .footer { margin-top: 20px; text-align: center; }

        @page {
            size: A4;
            margin: 20mm;
        }
    </style>
    {{ invoice_header_filter | raw }}
</head>
<body>
    <div class="invoice-container">
        {{ invoice_body_filter | raw }}
        <div class="invoice-header">
            <div class="text-left">
                <h3 class="mb-20 mt-20">{{ 'plugins/ecommerce::order.tax_invoice'|trans }}</h3>
            </div>
            <div class="text-right">
                <img src="{{ logo_full_path }}" style="max-height:60px;" class="mt-10" alt="{{ site_title }}">
            </div>
        </div>
        <hr />
        <!-- Vendor Details -->
        <div class="invoice-details mt-20">
            <div>
                {% if logo %}
                    <img src="{{ company_logo_full_path }}" style="max-height:70px;" alt="{{ company_name }}">
                {% endif %}
            </div>
            <div>
                {% if company_name %}
                <p><strong>{{ 'plugins/ecommerce::order.sold_by'|trans }}: </strong>{{ company_name }}</p>
                {% endif %}
                {% if company_address %}
                    <p><strong>{{ 'plugins/ecommerce::order.address'|trans }}: </strong>{{ company_address }}
                    {% if company_state %}
                    , {{ company_state }},
                    {% endif %}
                    {% if company_city %}
                    {{ company_city }},
                    {% endif %}
                    {% if company_zipcode %}
                    {{ company_zipcode }}</p>
                    {% endif %}
                {% endif %}
                {% if company_tax_id %}
                <p><strong>{{ 'plugins/ecommerce::setting.invoice.tax_id'|trans }}: </strong>{{ company_tax_id }}</p>
                {% endif %}
            </div>
        </div>
        <hr/>
        <!-- Customer Details -->
        <h4 class="mb-10 setionheading">{{ 'plugins/ecommerce::order.customer'|trans }} {{ 'plugins/ecommerce::order.billing_address'|trans }}:</h4>
        <div class="invoice-details">
            <div>
                {% if invoice.customer_name %}
                <p><strong>{{ 'plugins/ecommerce::order.customer'|trans }} {{ 'plugins/ecommerce::order.name'|trans }}:</strong> {{ invoice.customer_name }}</p>
                {% endif %}
                {% if invoice.customer_address %}
                <p><strong>{{ 'plugins/ecommerce::order.customer'|trans }} {{ 'plugins/ecommerce::order.address'|trans }}:</strong> {{ invoice.customer_address }}</p>
                {% endif %}
            </div>
            <div>
                {% if invoice.customer_phone %}
                <p><strong>{{ 'plugins/ecommerce::order.phone'|trans }}:</strong> {{ invoice.customer_phone }}</p>
                {% endif %}
                {% if invoice.customer_email %}
                <p><strong>{{ 'plugins/ecommerce::order.email'|trans }}:</strong> {{ invoice.customer_email }}</p>
                {% endif %}
            </div>
        </div>
        <hr/>
        <!-- Invoice Details -->
        <h4 class="mb-10 setionheading">{{ 'plugins/ecommerce::order.order_information'|trans }}:</h4>
        <div class="invoice-details">
            <div>
                <p class="orderId"><strong>{{ 'plugins/ecommerce::order.order_id'|trans }}: </strong> {{ invoice.reference.code }}</p>
                {% if invoice.created_at %}
                <p><strong>{{ 'plugins/ecommerce::order.invoice_date'|trans }}:</strong> {{ invoice.created_at|date('F d, Y') }}</p>
                {% endif %}
            </div>
            <div>
                {% if customer_tax_id %}
                <p><strong>{{ 'plugins/ecommerce::order.customer_GSTIN'|trans }}:</strong> {{ customer_tax_id }}</p>
                {% endif %}
                <p><strong>{{ 'plugins/ecommerce::order.invoice'|trans }} {{ 'plugins/ecommerce::order.code'|trans }}: </strong> {{ invoice.code }}</p>
            </div>
        </div>

        <table class="item-table">
            <thead>
                <tr>
                    <th class="product-name">{{ 'plugins/ecommerce::products.form.product'|trans }}</th>

                    <th>{{ 'plugins/ecommerce::products.form.quantity'|trans }}</th>

                    <th>{{ 'plugins/ecommerce::products.form.price'| trans }}</th>

                    {% if isIgst %}
                        <th>{{ 'plugins/ecommerce::products.form.igst'| trans }}</th>
                    {% else %}
                        <th>{{ 'plugins/ecommerce::products.form.cgst'| trans }}</th>
                        <th>{{ 'plugins/ecommerce::products.form.sgst'| trans }}</th>
                    {% endif %}

                    <th>{{ 'plugins/ecommerce::products.form.total'|trans }}</th>
                </tr>
            </thead>
            <tbody>
                {% for item in invoice.items %}
                    <tr>
                        <td>{{ item.name }}<br>
                            {% if item.options %}
                                {% if item.options.sku %}
                                    <div><small><strong>{{ 'plugins/ecommerce::invoice.detail.sku'|trans }}:</strong> {{ item.options.sku }}</small></div>
                                {% endif %}
                                {% if item.options.barcode %}
                                    <div><small><strong>{{ 'plugins/ecommerce::products.form.barcode'|trans }}:</strong> {{ item.options.barcode }}</small></div>
                                {% endif %}
                                {% if item.options.attributes %}
                                    <div><small>{{ 'plugins/ecommerce::invoice.detail.attributes'|trans }}: {{ item.options.attributes }}</small></div>
                                {% endif %}
                                {% if item.options.product_options %}
                                    <div><small>{{ 'plugins/ecommerce::invoice.detail.product_options'|trans }}: {{ item.options.product_options }}</small></div>
                                {% endif %}
                                {% if item.options.license_code %}
                                    <div><small>{{ 'plugins/ecommerce::invoice.detail.license_code'|trans }}: {{ item.options.license_code }}</small></div>
                                {% endif %}
                            {% endif %}
                        </td>

                        <td>{{ item.qty }}</td>

                        <td>{{ item.price|price_format }}</td>

                        {% if isIgst %}
                            <td class="right">{{ item.tax_amount|price_format }}</td>
                        {% else %}
                            <td class="right">{{ (item.tax_amount/2)|price_format }}</td>
                            <td class="right">{{ (item.tax_amount/2)|price_format }}</td>
                        {% endif %}

                        <td>{{ item.amount|price_format }}</td>
                    </tr>
                {% endfor %}
            </tbody>
            <tfoot>
                 <tr>
                    <th class="text-right">{{ 'plugins/ecommerce::order.totals'|trans }}</th>

                    <th>{{ total_quantity|number_format }}</th>

                    <th>{{ total_price|price_format }}</th>

                    {% if isIgst %}
                        <th>{{ total_tax|price_format }}</th>
                    {% else %}
                        <th>{{ (total_tax/2)|price_format }}</th>
                        <th>{{ (total_tax/2)|price_format }}</th>
                    {% endif %}

                    <th>{{ (invoice.sub_total + invoice.tax_amount)|price_format }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="invoice-details">
            <div>
                {% if payment_method %}
                <p>
                    {{ 'plugins/ecommerce::order.payment_method'|trans }}: <strong>{{ payment_method }}</strong>
                </p>
                {% endif %}

                {% if payment_status %}
                    <p>
                        {{ 'plugins/ecommerce::order.payment_status_label'|trans }}: <strong>{{ payment_status_label }}</strong>
                    </p>
                {% endif %}

                {% if invoice.paid_at %}
                    <p>
                        {{ 'plugins/ecommerce::order.paid_at'|trans }}: <strong>{{ invoice.paid_at|date('Y-m-d h:i:s A') }}</strong>
                    </p>
                {% endif %}

                {% if payment_description %}
                    <p>
                        {{ 'plugins/ecommerce::order.payment_info'|trans }}: <strong>{{ payment_description | raw }}</strong>
                    </p>
                {% endif %}
                {% if invoice.description %}
                    <hr>
                        <p class="m-0">{{ 'plugins/ecommerce::order.note'|trans }}: {{ invoice.description }}</p>
                    <hr>
                {% endif %}

                {% if company_signature_image %}
                    <hr>
                    <p><strong>{{ 'plugins/ecommerce::invoice.detail.seller_signature'|trans }}</strong></p>
                    <img src="{{ company_signature_image }}" style="max-width:80px;" />
                {% endif %}

                {{ invoice_payment_info_filter | raw }}

                {% if (get_ecommerce_setting('enable_invoice_stamp', 1) == 1) %}
                    <p class="mt-10">
                    {% if invoice.status == 'canceled' %}
                        <span class="stamp is-failed">
                            {{ invoice.status }}
                        </span>
                    {% elseif (payment_status_label) %}
                        <span class="stamp {% if payment_status == 'completed' %} is-completed {% else %} is-failed {% endif %}">
                            {{ payment_status_label }}
                        </span>
                    {% endif %}
                    </p>
                {% endif %}
            </div>
            <div>
                <table class="item-table">
                    <tbody>
                        {% if invoice.tax_amount > 0 %}
                            <tr>
                                <th class="large total">{{ 'plugins/ecommerce::products.form.sub_total'|trans }}</th>
                                <th class="large total">{{ invoice.sub_total|price_format }}</th>
                            </tr>
                            {% if isIgst %}
                                <tr>
                                    <td>{{ 'plugins/ecommerce::products.form.total'|trans }} {{ 'plugins/ecommerce::products.form.igst'|trans }}</td>
                                    <td>{{ invoice.tax_amount|price_format }}</td>
                                </tr>
                            {% else %}
                                <tr>
                                    <td>{{ 'plugins/ecommerce::products.form.total'|trans }} {{ 'plugins/ecommerce::products.form.cgst'|trans }}</td>
                                    <td>{{ (invoice.tax_amount/2)|price_format }}</td>
                                </tr>
                                <tr>
                                    <td>{{ 'plugins/ecommerce::products.form.total'|trans }} {{ 'plugins/ecommerce::products.form.sgst'|trans }}</td>
                                    <td>{{ (invoice.tax_amount/2)|price_format }}</td>
                                </tr>
                            {% endif %}
                            <tr>
                                <th class="large total">{{ 'plugins/ecommerce::products.form.total'|trans }}</th>
                                <th class="large total">{{ (invoice.sub_total + invoice.tax_amount)|price_format }}</th>
                            </tr>
                        {% endif %}
                        {% if invoice.coupon_code %}
                        <tr>
                            <td>{{ 'plugins/ecommerce::order.coupon_code_lable'|trans }}</td>
                            <td>{{ invoice.coupon_code | raw }}</td>
                        </tr>
                        {% endif %}

                        {% if invoice.discount_amount > 0 %}
                        <tr>
                            <td>{{ 'plugins/ecommerce::products.form.discount'|trans }}</td>
                            <td>{{ invoice.discount_amount|price_format }}</td>
                        </tr>
                        {% endif %}

                        {% if invoice.shipping_amount > 0 %}
                        <tr>
                            <td>{{ 'plugins/ecommerce::products.form.shipping_fee'|trans }}</td>
                            <td>{{ invoice.shipping_amount|price_format }}</td>
                        </tr>
                        {% endif %}

                        <tr>
                            <th class="large total">{{ 'plugins/ecommerce::order.grand_total'|trans }}</th>
                            <th class="large total">{{ invoice.amount|price_format }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="footer">
            <p class="mb-20 mt-20">{{ ecommerce_invoice_footer | raw }}</p>
        </div>
    </div>
</body>
</html>
