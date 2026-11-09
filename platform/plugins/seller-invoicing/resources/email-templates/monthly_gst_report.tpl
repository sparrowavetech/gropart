{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="bb-content bb-pb-0" align="center">
                    <table class="bb-icon bb-icon-lg bb-bg-green" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td valign="middle" align="center">
                                    <img src="{{ 'file-analytics' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">{{ 'plugins/seller-invoicing::seller-invoicing.email.monthly_gst_report_title' | trans }}</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content">
                    <div>{{ 'plugins/seller-invoicing::seller-invoicing.email.dear_seller' | trans({'seller_name': seller_name, 'store_name': store_name}) }}</div>
                    <p>{{ 'plugins/seller-invoicing::seller-invoicing.email.report_ready_message' | trans({'tax_period': tax_period}) }}</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-pt-0">
                    <table class="bb-row bb-mb-md" cellpadding="0" cellspacing="0" style="width: 100%; border: 1px solid #e0e0e0; border-collapse: collapse;">
                        <tbody>
                            <tr style="background-color: #f8f9fa;">
                                <td style="padding: 10px; border: 1px solid #e0e0e0; font-weight: bold;">{{ 'plugins/seller-invoicing::seller-invoicing.email.store_gstin' | trans }}</td>
                                <td style="padding: 10px; border: 1px solid #e0e0e0;"><strong>{{ seller_gstin }}</strong></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #e0e0e0;">{{ 'plugins/seller-invoicing::seller-invoicing.email.total_taxable_sales' | trans }}</td>
                                <td style="padding: 10px; border: 1px solid #e0e0e0;">₹{{ total_taxable_sales }}</td>
                            </tr>
                            <tr style="background-color: #f8f9fa;">
                                <td style="padding: 10px; border: 1px solid #e0e0e0;">{{ 'plugins/seller-invoicing::seller-invoicing.email.total_output_tax' | trans }}</td>
                                <td style="padding: 10px; border: 1px solid #e0e0e0;">₹{{ total_output_tax }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #e0e0e0; color: #198754; font-weight: bold;">{{ 'plugins/seller-invoicing::seller-invoicing.email.itc_marketplace_fees' | trans }}</td>
                                <td style="padding: 10px; border: 1px solid #e0e0e0; color: #198754; font-weight: bold;">₹{{ itc_marketplace_fees }}</td>
                            </tr>
                            <tr style="background-color: #fff3cd; font-weight: bold;">
                                <td style="padding: 10px; border: 1px solid #e0e0e0;">{{ 'plugins/seller-invoicing::seller-invoicing.email.net_gst_payable' | trans }}</td>
                                <td style="padding: 10px; border: 1px solid #e0e0e0;">₹{{ net_gst_payable }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="text-align: center; margin: 25px 0;">
                        <a href="{{ report_url }}" class="bb-btn bb-btn-primary" style="background-color: #206bc4; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;">
                            {{ 'plugins/seller-invoicing::seller-invoicing.email.download_reports_btn' | trans }}
                        </a>
                    </div>

                    <p style="color: #6c757d; font-size: 13px;">
                        {{ 'plugins/seller-invoicing::seller-invoicing.email.itc_note' | trans }}
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{ footer }}

