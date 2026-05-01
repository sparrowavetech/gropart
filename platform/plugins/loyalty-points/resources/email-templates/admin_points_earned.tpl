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
                                    <img src="{{ 'confetti' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">{{ 'plugins/loyalty-points::email-templates.admin_points_earned_heading' | trans }}</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p>{{ 'plugins/loyalty-points::email-templates.admin_points_earned_message' | trans({'customer': customer_name, 'points': points_earned, 'order_code': order_code}) }}</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-pt-0">
                    <table class="bb-table" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.customer_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ customer_name }}</td>
                            </tr>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.customer_email_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ customer_email }}</td>
                            </tr>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.points_earned_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">
                                    <span style="color: #28a745; font-weight: bold;">+{{ points_earned }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.order_code_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ order_code }}</td>
                            </tr>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.current_balance_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ current_balance }} {{ 'plugins/loyalty-points::email-templates.points' | trans }}</td>
                            </tr>
                            {% if level_name %}
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.level_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ level_name }}</td>
                            </tr>
                            {% endif %}
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
                    <table cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" class="bb-bg-blue bb-rounded bb-w-auto">
                                        <tr>
                                            <td align="center" valign="top" class="lh-1">
                                                <a href="{{ order_url }}" class="bb-btn bb-bg-blue bb-border-blue">
                                                    <span class="btn-span">{{ 'plugins/loyalty-points::email-templates.view_order' | trans }}</span>
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{ footer }}
