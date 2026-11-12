{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="bb-content bb-pb-0" align="center">
                    <table class="bb-icon bb-icon-lg bb-bg-blue" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td valign="middle" align="center">
                                    <img src="{{ 'mail' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.admin_new_request_heading' | trans }}</h1>
                </td>
            </tr>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0">
                        <tbody>
                            <tr>
                                <td class="bb-content">
                                    <p>{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.admin_new_request_intro' | trans }}</p>

                                    <table class="bb-table" cellspacing="0" cellpadding="0">
                                        <tbody>
                                            <tr>
                                                <td width="140px"><strong>{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.tables.store' | trans }}:</strong></td>
                                                <td class="bb-font-strong bb-text-left">{{ store_name }} ({{ vendor_name }})</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.tables.product' | trans }}:</strong></td>
                                                <td class="bb-font-strong bb-text-left">{{ product_name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Price:</strong></td>
                                                <td class="bb-font-strong bb-text-left">{{ product_price }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.tables.scheme' | trans }}:</strong></td>
                                                <td class="bb-font-strong bb-text-left">{{ requested_scheme }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.tables.subvention' | trans }}:</strong></td>
                                                <td class="bb-font-strong bb-text-left">{{ subvention_willingness }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Submitted At:</strong></td>
                                                <td class="bb-font-strong bb-text-left">{{ created_at }}</td>
                                            </tr>
                                            {% if notes %}
                                            <tr>
                                                <td><strong>{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.tables.notes' | trans }}:</strong></td>
                                                <td class="bb-text-left"><i>{{ notes }}</i></td>
                                            </tr>
                                            {% endif %}
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            {% if request_url %}
                            <tr>
                                <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl" align="center">
                                    <a href="{{ request_url }}" class="bb-btn bb-bg-blue bb-border-blue" style="color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 4px; display: inline-block;">{{ 'plugins/razorpay-easy-emi::razorpay-easy-emi.email.review_request_btn' | trans }}</a>
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

