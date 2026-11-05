<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - {{ site_title }} - {{ invoice.number }}</title>
    <style>
        html, body {
            font-family: 'DejaVu Sans', Arial, sans-serif !important;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 10px;
            line-height: 1.4;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 20px;
            background: #fff;
        }
        .header-table, .details-table, .items-table, .totals-table, .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #111;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .bold {
            font-weight: bold;
        }
        .section-heading {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
            margin-bottom: 8px;
            color: #222;
        }
        .order-meta-box {
            background: #f8f9fa;
            border: 1px dashed #ccc;
            padding: 8px 12px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .items-table th {
            background-color: #f1f3f5;
            border: 1px solid #dee2e6;
            padding: 8px 6px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .items-table td {
            border: 1px solid #dee2e6;
            padding: 8px 6px;
            font-size: 10px;
        }
        .totals-table {
            width: 250px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-table td {
            font-size: 11px;
            padding: 4px 6px;
        }
        .totals-table tr.grand-total td {
            font-size: 13px;
            font-weight: bold;
            border-top: 2px solid #222;
            border-bottom: 2px solid #222;
            color: #0d6efd;
            padding: 6px;
        }
        .signatory-box {
            margin-top: 20px;
            text-align: right;
        }
        .signatory-img {
            max-height: 50px;
            margin-bottom: 5px;
        }
        .footer-note {
            margin-top: 25px;
            border-top: 1px solid #eee;
            padding-top: 10px;
            font-size: 9px;
            color: #777;
        }
        @page {
            size: A4;
            margin: 15mm;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div class="header-title">TAX INVOICE</div>
                    <div class="header-subtitle">Marketplace Seller Services</div>
                </td>
                <td style="width: 40%; vertical-align: top;" class="text-right">
                    {% if company.logo %}
                        <img src="{{ company.logo }}" style="max-height: 50px; max-width: 250px; width: auto; object-fit: contain;" alt="{{ site_title }}">
                    {% else %}
                        <h2 style="margin: 0; color: #0d6efd;">{{ site_title }}</h2>
                    {% endif %}
                </td>
            </tr>
        </table>

        <!-- Details Grid -->
        <table class="details-table">
            <tr>
                <!-- Supplier / Operator Details -->
                <td style="width: 50%; vertical-align: top; padding-right: 15px;">
                    <div class="section-heading">Service Provider (Invoice From):</div>
                    <div class="bold" style="font-size: 12px;">{{ company.name }}</div>
                    <div>{{ company.address }}</div>
                    {% if company.city or company.state %}
                        <div>{{ company.city }}{% if company.city and company.state %}, {% endif %}{{ company.state }}</div>
                    {% endif %}
                    {% if company.country %}
                        <div>{{ company.country }}</div>
                    {% endif %}
                    {% if company.tax_id %}
                        <div style="margin-top: 4px;"><span class="bold">{{ taxes.tax_id_label|default('GSTIN') }}:</span> {{ company.tax_id }}</div>
                    {% endif %}
                    {% if company.phone or company.email %}
                        <div><span class="bold">Contact:</span> {{ company.phone }} {% if company.email %}| {{ company.email }}{% endif %}</div>
                    {% endif %}
                </td>

                <!-- Invoice Meta -->
                <td style="width: 50%; vertical-align: top; padding-left: 15px;">
                    <div class="section-heading">Invoice Information:</div>
                    <div class="order-meta-box">
                        <div><span class="bold">Invoice No:</span> <span style="font-size: 12px; color: #0d6efd; font-weight: bold;">{{ invoice.number }}</span></div>
                        <div><span class="bold">Invoice Date:</span> {{ invoice.created_at }}</div>
                        {% if invoice.order_id %}
                            <div><span class="bold">Order Reference:</span> #{{ invoice.order_id }}</div>
                        {% endif %}
                        {% if invoice.order_date %}
                            <div><span class="bold">Order Date:</span> {{ invoice.order_date }}</div>
                        {% endif %}
                        {% if seller.place_of_supply %}
                            <div><span class="bold">Place of Supply:</span> {{ seller.place_of_supply }}</div>
                        {% endif %}
                    </div>
                </td>
            </tr>
            <tr>
                <!-- Recipient / Seller Details -->
                <td colspan="2" style="padding-top: 10px;">
                    <div class="section-heading">Recipient / Vendor (Invoice To):</div>
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 50%; vertical-align: top;">
                                <div class="bold" style="font-size: 12px;">{{ seller.name }}</div>
                                {% if seller.owner_name and seller.owner_name != seller.name %}
                                    <div><span class="bold">Attn:</span> {{ seller.owner_name }}</div>
                                {% endif %}
                                <div>{{ seller.address }}</div>
                                {% if seller.state or seller.country %}
                                    <div>{{ seller.state }}{% if seller.state and seller.country %}, {% endif %}{{ seller.country }}</div>
                                {% endif %}
                            </td>
                            <td style="width: 50%; vertical-align: top; text-align: right;">
                                {% if seller.tax_id %}
                                    <div><span class="bold">{{ taxes.tax_id_label|default('GSTIN') }}:</span> {{ seller.tax_id }}</div>
                                {% endif %}
                                {% if seller.phone %}
                                    <div><span class="bold">Phone:</span> {{ seller.phone }}</div>
                                {% endif %}
                                {% if seller.email %}
                                    <div><span class="bold">Email:</span> {{ seller.email }}</div>
                                {% endif %}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Line Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 35%;">Service Description</th>
                    <th class="text-center" style="width: 12%;">SAC / Code</th>
                    <th class="text-right" style="width: 15%;">Taxable ({{ invoice.currency }})</th>
                    <th class="text-center" style="width: 10%;">Rate</th>
                    {% if taxes.is_india %}
                        {% if taxes.is_igst %}
                            <th class="text-right" style="width: 14%;">IGST ({{ invoice.currency }})</th>
                        {% else %}
                            <th class="text-right" style="width: 10%;">CGST</th>
                            <th class="text-right" style="width: 10%;">SGST</th>
                        {% endif %}
                    {% else %}
                        <th class="text-right" style="width: 14%;">Tax ({{ invoice.currency }})</th>
                    {% endif %}
                    <th class="text-right" style="width: 14%;">Total ({{ invoice.currency }})</th>
                </tr>
            </thead>
            <tbody>
                {% for line in lines %}
                    <tr>
                        <td class="text-left">
                            <div class="bold">{{ line.name }}</div>
                        </td>
                        <td class="text-center">{{ line.sac_code }}</td>
                        <td class="text-right" style="white-space: nowrap;">{{ invoice.symbol|default('₹') }} {{ (line.taxable_amount|default(0))|number_format(2) }}</td>
                        <td class="text-center">{{ line.tax_rate }}%</td>
                        {% if taxes.is_india %}
                            {% if taxes.is_igst %}
                                <td class="text-right" style="white-space: nowrap;">{{ invoice.symbol|default('₹') }} {{ (line.igst_amount|default(0))|number_format(2) }}</td>
                            {% else %}
                                <td class="text-right" style="white-space: nowrap;">{{ invoice.symbol|default('₹') }} {{ (line.cgst_amount|default(0))|number_format(2) }}</td>
                                <td class="text-right" style="white-space: nowrap;">{{ invoice.symbol|default('₹') }} {{ (line.sgst_amount|default(0))|number_format(2) }}</td>
                            {% endif %}
                        {% else %}
                            <td class="text-right" style="white-space: nowrap;">{{ invoice.symbol|default('₹') }} {{ (line.tax_amount|default(0))|number_format(2) }}</td>
                        {% endif %}
                        <td class="text-right bold" style="white-space: nowrap;">{{ invoice.symbol|default('₹') }} {{ (line.total_amount|default(0))|number_format(2) }}</td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>

        <!-- Totals & Signatory -->
        <table style="width: 100%; margin-top: 10px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div style="font-size: 10px; color: #555;">
                        <span class="bold">Tax Specification:</span> {{ taxes.rate_label }}
                    </div>
                    <div style="font-size: 10px; color: #555; margin-top: 5px;">
                        This is a computer-generated tax invoice for marketplace facilitation services rendered to the vendor.
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <table class="totals-table">
                        <tr>
                            <td class="text-right bold" style="padding-right: 15px; color: #333;">Taxable Subtotal:</td>
                            <td class="text-right" style="white-space: nowrap; width: 85px;">{{ invoice.symbol|default('₹') }} {{ (invoice.subtotal|default(0))|number_format(2) }}</td>
                        </tr>
                        {% if taxes.is_india %}
                            {% if taxes.is_igst %}
                                <tr>
                                    <td class="text-right bold" style="padding-right: 15px; color: #333;">IGST Total:</td>
                                    <td class="text-right" style="white-space: nowrap; width: 85px;">{{ invoice.symbol|default('₹') }} {{ (taxes.igst_amount|default(0))|number_format(2) }}</td>
                                </tr>
                            {% else %}
                                <tr>
                                    <td class="text-right bold" style="padding-right: 15px; color: #333;">CGST Total:</td>
                                    <td class="text-right" style="white-space: nowrap; width: 85px;">{{ invoice.symbol|default('₹') }} {{ (taxes.cgst_amount|default(0))|number_format(2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-right bold" style="padding-right: 15px; color: #333;">SGST Total:</td>
                                    <td class="text-right" style="white-space: nowrap; width: 85px;">{{ invoice.symbol|default('₹') }} {{ (taxes.sgst_amount|default(0))|number_format(2) }}</td>
                                </tr>
                            {% endif %}
                        {% else %}
                            <tr>
                                <td class="text-right bold" style="padding-right: 15px; color: #333;">Total {{ taxes.tax_label|default('Tax') }}:</td>
                                <td class="text-right" style="white-space: nowrap; width: 85px;">{{ invoice.symbol|default('₹') }} {{ (invoice.tax_amount|default(0))|number_format(2) }}</td>
                            </tr>
                        {% endif %}
                        <tr class="grand-total">
                            <td class="text-right" style="padding-right: 15px;">Grand Total:</td>
                            <td class="text-right" style="white-space: nowrap; width: 85px;">{{ invoice.symbol|default('₹') }} {{ (invoice.grand_total|default(0))|number_format(2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Signatory Block -->
        <div class="signatory-box">
            {% if authorised_signatory.image %}
                <div>
                    <img src="{{ authorised_signatory.image }}" class="signatory-img" alt="Signature">
                </div>
            {% endif %}
            <div class="bold" style="font-size: 11px;">{{ authorised_signatory.name|default(company.name) }}</div>
            <div style="font-size: 9px; color: #666;">Authorised Signatory</div>
        </div>

        <!-- Footer -->
        <div class="footer-note">
            <div><span class="bold">Registered Office:</span> {{ company.address }}, {{ company.city }}, {{ company.state }}, {{ company.country }}</div>
            <div><span class="bold">Support:</span> {{ company.email }} | {{ company.phone }}</div>
        </div>
    </div>
</body>
</html>

