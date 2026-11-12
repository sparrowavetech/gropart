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
                                    <img src="{{ 'mail' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vendor_update_heading' | trans }}</h1>
                </td>
            </tr>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0">
                        <tbody>
                            <tr>
                                <td class="bb-content">
                                    <p>Dear {{ vendor_name }} ({{ store_name }}),</p>
                                    <p>{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.vendor_update_intro' | trans }}</p>

                                    <table class="bb-table" cellspacing="0" cellpadding="0">
                                        <tbody>
                                            <tr>
                                                <td width="140px"><strong>{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.tables.product' | trans }}:</strong></td>
                                                <td class="bb-font-strong bb-text-left">{{ product_name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.tables.scheme' | trans }}:</strong></td>
                                                <td class="bb-font-strong bb-text-left">{{ requested_scheme }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td class="bb-font-strong bb-text-left">
                                                    <span style="font-weight: bold; color: {% if status == 'Approved' %}#28a745{% else %}#dc3545{% endif %};">{{ status }}</span>
                                                </td>
                                            </tr>
                                            {% if approved_offer_id %}
                                            <tr>
                                                <td><strong>{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.tables.offer_id' | trans }}:</strong></td>
                                                <td class="bb-font-strong bb-text-left">{{ approved_offer_id }}</td>
                                            </tr>
                                            {% endif %}
                                            {% if admin_remarks %}
                                            <tr>
                                                <td><strong>{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.tables.admin_remarks' | trans }}:</strong></td>
                                                <td class="bb-text-left"><i>{{ admin_remarks }}</i></td>
                                            </tr>
                                            {% endif %}
                                            <tr>
                                                <td><strong>Action Date:</strong></td>
                                                <td class="bb-font-strong bb-text-left">{{ reviewed_at }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            {% if product_edit_url %}
                            <tr>
                                <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl" align="center">
                                    <a href="{{ product_edit_url }}" class="bb-btn bb-bg-green bb-border-green" style="color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 4px; display: inline-block;">{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.view_product_btn' | trans }}</a>
                                </td>
                            </tr>
                            {% endif %}
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{ footer }}

