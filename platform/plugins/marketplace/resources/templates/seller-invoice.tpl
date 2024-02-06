<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ 'plugins/marketplace::invoice-template.invoice_for_order'|trans }} - {{ site_title }} - {{ invoice.seller_inv_code }}</title>
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

        .item-table th.product-name { width: 20%; }

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

        .footer { margin-top: 20px; border-top: 1px solid #000; padding-bottom: 20px; }

        @page {
            size: A4;
            margin: 20mm;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="text-left">
                <h3 class="mb-10 mt-20">{{ 'plugins/marketplace::invoice-template.tax_invoice'|trans }}</h3>
                <h6 class="mb-20 mt-10">{{ 'plugins/marketplace::invoice-template.seller_tax_invoice'|trans }}</h6>
            </div>
            <div class="text-right">
                <img src="{{ company_logo_full_path }}" style="max-height:60px;" class="mt-10" alt="{{ site_title }}">
            </div>
        </div>
        <hr />

        <div class="invoice-details">
            <!-- Company Details -->
            <div>
                <h4 class="mb-10 setionheading mt-2">{{ 'plugins/marketplace::invoice-template.invoice_from'|trans }}:</h4>
                {% if company_name %}
                    <p><strong>{{ company_name }}</strong></p>
                {% endif %}
                {% if company_address %}
                    <p><strong>{{ 'plugins/marketplace::invoice-template.address'|trans }}: </strong>{{ company_address }}
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
                <p><strong>{{ 'plugins/marketplace::invoice-template.tax_id'|trans }}: </strong>{{ company_tax_id }}</p>
                {% endif %}
            </div>
            <!-- Invoice Details -->
            <div>
                <h4 class="mb-10 setionheading">{{ 'plugins/marketplace::invoice-template.order_information'|trans }}:</h4>
                <p class="orderId"><strong>{{ 'plugins/marketplace::invoice-template.invoice_id'|trans }}: </strong> {{ invoice.seller_inv_code }}</p>
                <p class="orderId"><strong>{{ 'plugins/marketplace::invoice-template.order_id'|trans }}: </strong> {{ order_id }}</p>
                {% if invoice.created_at %}
                <p><strong>{{ 'plugins/marketplace::invoice-template.invoice_date'|trans }}:</strong> {{ invoice.created_at|date('F d, Y') }}</p>
                {% endif %}
            </div>
        </div>
        <hr/>

        <!-- Customer Details -->
        <h4 class="mb-10 setionheading">{{ 'plugins/marketplace::invoice-template.invoice_to'|trans }}:</h4>
        <div class="invoice-details">
            <div>
                {% if seller_company_name %}
                <p><strong>{{ 'plugins/marketplace::invoice-template.seller_company'|trans }}: </strong>{{ seller_company_name }}</p>
                {% endif %}
                {% if seller_company_address %}
                    <p><strong>{{ 'plugins/marketplace::invoice-template.address'|trans }}: </strong>{{ seller_company_address }}
                    {% if seller_state %}
                    , {{ seller_state }},
                    {% endif %}
                    {% if seller_city %}
                        {{ seller_city }},
                    {% endif %}
                    {% if seller_zip_code %}
                        {{ seller_zip_code }}</p>
                    {% endif %}
                {% endif %}
                {% if seller_state %}
                <p><strong>{{ 'plugins/marketplace::invoice-template.supply_place'|trans }}: </strong>{{ seller_state }}</p>
                {% endif %}
            </div>
            <div>
                {% if seller_owner_name %}
                <p><strong>{{ 'plugins/marketplace::invoice-template.seller_name'|trans }}: </strong>{{ seller_owner_name }}</p>
                {% endif %}
                {% if seller_owner_phone %}
                <p><strong>{{ 'plugins/marketplace::invoice-template.phone'|trans }}:</strong> {{ seller_owner_phone }}</p>
                {% endif %}
                {% if seller_owner_email %}
                <p><strong>{{ 'plugins/marketplace::invoice-template.email'|trans }}:</strong> {{ seller_owner_email }}</p>
                {% endif %}
                {% if seller_company_tax_id %}
                <p><strong>{{ 'plugins/marketplace::invoice-template.tax_id'|trans }}: </strong>{{ seller_company_tax_id }}</p>
                {% endif %}
            </div>
        </div>
        <hr/>

        <!-- Order Details -->
        <table class="item-table">
            <thead>
                <tr>
                    <th class="product-name">{{ 'plugins/marketplace::invoice-template.detail.item_name'|trans }}</th>

                    <th>{{ 'plugins/marketplace::invoice-template.detail.SAC_Code'|trans }}</th>

                    <th>{{ 'plugins/marketplace::invoice-template.detail.taxable_value'| trans }}</th>

                    <th>{{ 'plugins/marketplace::invoice-template.detail.tax_rate'| trans }}</th>

                    {% if invoice.fee_tax_rate > 0 %}
                        {% if isIgst %}
                            <th>{{ 'plugins/marketplace::invoice-template.detail.igst'| trans }}</th>
                        {% else %}
                            <th>{{ 'plugins/marketplace::invoice-template.detail.cgst'| trans }}</th>
                            <th>{{ 'plugins/marketplace::invoice-template.detail.sgst'| trans }}</th>
                        {% endif %}
                    {% endif %}

                    <th>{{ 'plugins/marketplace::invoice-template.detail.total'|trans }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ 'plugins/marketplace::invoice-template.detail.platform_fee'|trans }}</td>

                    <td>{{ 'plugins/marketplace::invoice-template.detail.service_sac'|trans }}</td>

                    <td>{{ invoice.platform_fee|price_format }}</td>

                    <td>{{ invoice.fee_tax_rate }}%</td>

                    {% if invoice.fee_tax_rate > 0 %}
                        {% if isIgst %}
                            <td class="right">{{ ((invoice.platform_fee*invoice.fee_tax_rate)/100)|price_format }}</td>
                        {% else %}
                            <td class="right">{{ (((invoice.platform_fee*invoice.fee_tax_rate)/100)/2)|price_format }}</td>
                            <td class="right">{{ (((invoice.platform_fee*invoice.fee_tax_rate)/100)/2)|price_format }}</td>
                        {% endif %}
                    {% endif %}

                    <td>{{ (invoice.platform_fee+((invoice.platform_fee*invoice.fee_tax_rate)/100))|price_format }}</td>
                </tr>
                <tr>
                    <td>{{ 'plugins/marketplace::invoice-template.detail.commission_fee'|trans }}</td>

                    <td>{{ 'plugins/marketplace::invoice-template.detail.service_sac'|trans }}</td>

                    <td>{{ invoice.commission_fee|price_format }}</td>

                    <td>{{ invoice.fee_tax_rate }}%</td>

                    {% if invoice.fee_tax_rate > 0 %}
                        {% if isIgst %}
                            <td class="right">{{ ((invoice.commission_fee*invoice.fee_tax_rate)/100)|price_format }}</td>
                        {% else %}
                            <td class="right">{{ (((invoice.commission_fee*invoice.fee_tax_rate)/100)/2)|price_format }}</td>
                            <td class="right">{{ (((invoice.commission_fee*invoice.fee_tax_rate)/100)/2)|price_format }}</td>
                        {% endif %}
                    {% endif %}

                    <td>{{ (invoice.commission_fee+((invoice.commission_fee*invoice.fee_tax_rate)/100))|price_format }}</td>
                </tr>
                <tr>
                    <td>{{ 'plugins/marketplace::invoice-template.detail.shipping_fee'|trans }}</td>

                    <td>{{ 'plugins/marketplace::invoice-template.detail.shipping_sac'|trans }}</td>

                    <td>{{ ((invoice.shipping_cost*100)/(100+invoice.fee_tax_rate))|price_format }}</td>

                    <td>{{ invoice.fee_tax_rate }}%</td>

                    {% if invoice.fee_tax_rate > 0 %}
                        {% if isIgst %}
                            <td class="right">{{ (invoice.shipping_cost-((invoice.shipping_cost*100)/(100+invoice.fee_tax_rate)))|price_format }}</td>
                        {% else %}
                            <td class="right">{{ ((invoice.shipping_cost-((invoice.shipping_cost*100)/(100+invoice.fee_tax_rate)))/2)|price_format }}</td>
                            <td class="right">{{ ((invoice.shipping_cost-((invoice.shipping_cost*100)/(100+invoice.fee_tax_rate)))/2)|price_format }}</td>
                        {% endif %}
                    {% endif %}

                    <td>{{ invoice.shipping_cost|price_format }}</td>
                </tr>
            </tbody>
            <tfoot>
                 <tr>
                    <th class="text-right" colspan="2">{{ 'plugins/marketplace::invoice-template.detail.total'|trans }}</th>

                    <th colspan="2" class="text-left">{{ ((invoice.commission_fee+invoice.platform_fee+(invoice.shipping_cost*100)/(100+invoice.fee_tax_rate)))|price_format }}</th>

                    {% if invoice.fee_tax_rate > 0 %}
                        {% if isIgst %}
                            <th>{{ (((invoice.platform_fee*invoice.fee_tax_rate)/100)+((invoice.commission_fee*invoice.fee_tax_rate)/100)+(invoice.shipping_cost-((invoice.shipping_cost*100)/(100+invoice.fee_tax_rate))))|price_format }}</th>
                        {% else %}
                            <th>{{ ((((invoice.platform_fee*invoice.fee_tax_rate)/100)+((invoice.commission_fee*invoice.fee_tax_rate)/100)+(invoice.shipping_cost-((invoice.shipping_cost*100)/(100+invoice.fee_tax_rate))))/2)|price_format }}</th>
                            <th>{{ ((((invoice.platform_fee*invoice.fee_tax_rate)/100)+((invoice.commission_fee*invoice.fee_tax_rate)/100)+(invoice.shipping_cost-((invoice.shipping_cost*100)/(100+invoice.fee_tax_rate))))/2)|price_format }}</th>
                        {% endif %}
                    {% endif %}

                    <th>{{ invoice.fee|price_format }}</th>
                </tr>
            </tfoot>
        </table>

        <!-- Total Details -->
        <div class="invoice-details">
            <div>
                <table class="item-table">
                    <tbody>
                        <tr>
                            <td class="large total">{{ 'plugins/marketplace::invoice-template.detail.total'|trans }}</td>
                            <td class="large total">{{ invoice.fee|price_format }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {% if authorised_signatory_image %}
        <div class="text-right">
            <img src="{{ authorised_signatory_image }}" style="max-width:80px;" />
            <p><strong>{{ 'plugins/marketplace::invoice-template.authorised_signatory'|trans }}</strong></p>
        </div>
        {% endif %}

        <div class="footer text-left">
            <p><strong>{{ 'plugins/marketplace::invoice-template.regd_office'|trans }}</strong> {{ company_address }}
                    {% if seller_state %}
                    , {{ seller_state }},
                    {% endif %}
                    {% if seller_city %}
                        {{ seller_city }},
                    {% endif %}
                    {% if seller_zip_code %}
                        {{ seller_zip_code }}</p>
                    {% endif %}</p>
            <p><strong>{{ 'plugins/marketplace::invoice-template.contact_details'|trans }}</strong> {{ company_phone }} | {{ company_email }}</p>
        </div>
    </div>
</body>
</html>
