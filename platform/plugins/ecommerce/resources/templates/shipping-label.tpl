<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>{{ 'plugins/ecommerce::shipping.shipping_label.name'|trans }} {{ shipment.code }}</title>

        {{ settings.font_css }}

        <style>
            @page {
                margin: 0;
            }

            * {
                margin: 0;
            }

            body {
                font-size: 12px;
                font-family: '{{ settings.font_family }}', Arial, sans-serif !important;
            }

            table {
                border-collapse: collapse;
                width: 100%
            }

            table tr td {
                padding: 0
            }

            {{ settings.extra_css }}

            h4
        </style>

        {{ settings.header_html }}
    </head>
    <body>
        <div style="height: 94%; border: 3px dashed black; margin: 20px;">
            <div style="padding: 20px; padding-bottom: 10px; padding-top: 10px; border-bottom: 1px solid black;">
                <table>
                    <tr>
                        <td style="vertical-align: top; width: 10%">
                            <img src="{{ sender.logo }}" alt="{{ sender.name }}" style="max-width: 120px; width: 100%: height: auto; padding-right:10px">
                        </td>
                        <td style="vertical-align: top">
                            <strong>{{ 'plugins/ecommerce::shipping.shipping_label.sender'|trans }}:</strong><br/>
                            {% if sender.name %} <h4><em>{{ sender.name }}</em></h4> {% endif %}
                            {% if sender.full_address %} <p><em>{{ sender.full_address }}</em></p> {% endif %}
                            {% if sender.phone %} <p><em>{{ sender.phone }}</em></p> {% endif %}
                            {% if sender.email %} <p><em>{{ sender.email }}</em></p> {% endif %}
                        </td>
                    </tr>
                </table>
            </div>
            <div style="padding: 20px; padding-bottom: 10px; padding-top: 10px; border-bottom: 1px solid black">
                <strong>{{ 'plugins/ecommerce::shipping.shipping_label.reciever'|trans }}:</strong> <small><em>{{ 'plugins/ecommerce::shipping.shipping_label.sender_note'|trans }}</em></small><br/>
                {% if receiver.name %} <h2 style="margin-bottom: 10px;font-size: 1px"><em>{{ receiver.name }}</em></h2> {% endif %}
                {% if receiver.full_address %} <h4 style="margin-bottom: 6px"><em>{{ receiver.full_address }}</em></h4> {% endif %}
                {% if receiver.email %} <h4 style="margin-bottom: 6px"><em>{{ receiver.email }}</em></h4> {% endif %}
                {% if receiver.phone %} <h4 style="margin-bottom: 6px"><em>{{ receiver.phone }}</em></h4> {% endif %}
            </div>

            <div style="padding: 20px; padding-bottom: 10px; padding-top: 10px; border-bottom: 1px solid black">
                <table>
                    <tr>
                        <td style="padding-bottom: 10px;">
                            <span>{{ 'plugins/ecommerce::shipping.shipment_id'|trans }}:</span>
                            <h3 style="font-size: 10px">{{ shipment.code }}</h3>
                        </td>
                        <td style="padding-bottom: 10px;">
                            <span>{{ 'plugins/ecommerce::shipping.order_id'|trans }}:</span>
                            <h3 style="font-size: 10px">{{ shipment.order_number }}</h3>
                        </td>
                        <td style="padding-bottom: 10px;">
                            <span>{{ 'plugins/ecommerce::shipping.shipping_label.order_date'|trans }}:</span>
                            <h3 style="font-size: 10px">{{ shipment.created_at|date('d-m-Y') }}</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span>{{ 'plugins/ecommerce::shipping.shipping_method'|trans }}:</span>
                            <h3 style="font-size: 10px">{{ shipment.shipping_method }}</h3>
                        </td>
                        <td>
                            <span>{{ 'plugins/ecommerce::shipping.weight_unit'|trans({unit: shipment.weight_unit}) }}:</span>
                            <h3 style="font-size: 10px">{{ shipment.weight }} {{ shipment.weight_unit }}</h3>
                        </td>
                        <td>
                            <span>{{ 'plugins/ecommerce::shipping.shipping_fee'|trans }}:</span>
                            <h3 style="font-size: 10px">{{ shipment.shipping_fee }}</h3>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="padding: 20px; padding-bottom: 10px; padding-top: 10px;">
                {% if shipment.shipping_company_name %}
                    <div style="margin-bottom: 5px; overflow-wrap: break-word;">
                        <span>{{ 'plugins/ecommerce::shipping.shipping_label.shipping_company'|trans }}:</span>
                        <strong>{{ shipment.shipping_company_name }}</strong>
                        <p><strong>{{ 'plugins/ecommerce::shipping.tracking_id'|trans }}:</strong> {{ shipment.tracking_id }}</p>
                        <p><strong>{{ 'plugins/ecommerce::shipping.tracking_link'|trans }}:</strong> {{ shipment.tracking_link }}</p>
                    </div>
                {% endif %}

                {% if shipment.note %}
                    <div style="margin-bottom: 5px; overflow-wrap: break-word;">
                        <span>{{ 'plugins/ecommerce::shipping.delivery_note'|trans }}:</span>
                        <strong>{{ shipment.note }}</strong>
                    </div>
                {% endif %}

                {% if receiver.note %}
                <div style="margin-bottom: 5px; overflow-wrap: break-word;">
                    <span>{{ 'plugins/ecommerce::shipping.customer_note'|trans }}:</span>
                    <strong>{{ receiver.note }}</strong>
                </div>
                {% endif %}

                <table>
                    <tr>
                        <td>
                            <img src="data:image/svg+xml;base64,{{ shipment.qr_code }}" style="max-height: 160px; width: auto%; height: auto;" alt="QR code">
                        </td>
                        <td style="font-size: 12px;">
                            {{ 'plugins/ecommerce::shipping.shipping_label.scan_qr_code'|trans }}
                        </td>
                    </tr>
                </table>
            </div>
            <div style="padding: 20px; padding-bottom: 10px; padding-top: 10px; border-top: 1px solid black">
                <table>
                    <tr>
                        <td colspan="2"><p>{{ 'plugins/ecommerce::shipping.shipping_label.orderThrough'|trans }}</p></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; width: 10%;">
                            <img src="{{ sender.brandlogo }}" alt="{{ sender.companyName }}" style="max-width: 120px; width: 100%: height: auto; padding-right:10px">
                        </td>
                        <td style="vertical-align: top; text-align: right;">
                            <h2>{{ sender.companyName }}</h2>
                            <p><strong>{{ 'plugins/ecommerce::shipping.shipping_label.companyGST'|trans }}:</strong> {{ sender.companyGST }}</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{ settings.footer_html }}
    </body>
</html>
