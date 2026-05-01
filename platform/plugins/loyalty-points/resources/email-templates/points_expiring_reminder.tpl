{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="bb-content bb-pb-0" align="center">
                    <table class="bb-icon bb-icon-lg bb-bg-orange" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td valign="middle" align="center">
                                    <img src="{{ 'clock' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">{{ 'plugins/loyalty-points::email-templates.points_expiring_heading' | trans }}</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p class="h1">{{ 'plugins/loyalty-points::email-templates.greeting' | trans({'customer_name': customer_name}) }}</p>
                    <p>{{ 'plugins/loyalty-points::email-templates.points_expiring_message' | trans({'points': expiring_points, 'date': expiry_date}) }}</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-pt-0">
                    <table class="bb-table" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.expiring_points_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">
                                    <span style="color: #fd7e14; font-weight: bold;">{{ expiring_points }} {{ 'plugins/loyalty-points::email-templates.points' | trans }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.expiry_date_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ expiry_date }}</td>
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
            <tr>
                <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
                    <p>{{ 'plugins/loyalty-points::email-templates.points_expiring_cta' | trans }}</p>
                    <table cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" class="bb-bg-orange bb-rounded bb-w-auto">
                                        <tr>
                                            <td align="center" valign="top" class="lh-1">
                                                <a href="{{ site_url }}" class="bb-btn bb-bg-orange bb-border-orange">
                                                    <span class="btn-span">{{ 'plugins/loyalty-points::email-templates.redeem_now' | trans }}</span>
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
