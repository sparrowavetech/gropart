{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="bb-content bb-pb-0" align="center">
                    <table class="bb-icon bb-icon-lg bb-bg-yellow" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td valign="middle" align="center">
                                    <img src="{{ 'star' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">{{ 'plugins/loyalty-points::email-templates.level_upgraded_heading' | trans }}</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p class="h1">{{ 'plugins/loyalty-points::email-templates.congratulations' | trans({'customer_name': customer_name}) }}</p>
                    <p>{{ 'plugins/loyalty-points::email-templates.level_upgraded_message' | trans({'new_level': new_level_name}) }}</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-pt-0">
                    <table class="bb-table" cellspacing="0" cellpadding="0">
                        <tbody>
                            {% if old_level_name %}
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.previous_level_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ old_level_name }}</td>
                            </tr>
                            {% endif %}
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.new_level_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">
                                    <span style="color: #ffc107; font-weight: bold;">{{ new_level_name }}</span>
                                </td>
                            </tr>
                            {% if earning_rate and earning_rate > 1 %}
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.earning_rate_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ earning_rate }}x {{ 'plugins/loyalty-points::email-templates.points' | trans }}</td>
                            </tr>
                            {% endif %}
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.lifetime_points_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ lifetime_points }} {{ 'plugins/loyalty-points::email-templates.points' | trans }}</td>
                            </tr>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.current_balance_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ current_balance }} {{ 'plugins/loyalty-points::email-templates.points' | trans }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            {% if level_benefits %}
            <tr>
                <td class="bb-content bb-pt-0">
                    <h3 class="bb-m-0 bb-mb-sm">{{ 'plugins/loyalty-points::email-templates.your_benefits' | trans }}</h3>
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                        {{ level_benefits | raw }}
                    </div>
                </td>
            </tr>
            {% endif %}
            <tr>
                <td class="bb-content bb-text-center bb-pt-md bb-pb-xl">
                    <p>{{ 'plugins/loyalty-points::email-templates.level_upgraded_cta' | trans }}</p>
                    <table cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" class="bb-bg-yellow bb-rounded bb-w-auto">
                                        <tr>
                                            <td align="center" valign="top" class="lh-1">
                                                <a href="{{ site_url }}" class="bb-btn bb-bg-yellow bb-border-yellow" style="color: #000;">
                                                    <span class="btn-span">{{ 'plugins/loyalty-points::email-templates.shop_now' | trans }}</span>
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
