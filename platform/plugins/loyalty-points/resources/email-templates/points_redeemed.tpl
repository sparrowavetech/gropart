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
                                    <img src="{{ 'gift' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">{{ 'plugins/loyalty-points::email-templates.points_redeemed_heading' | trans }}</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p class="h1">{{ 'plugins/loyalty-points::email-templates.greeting' | trans({'customer_name': customer_name}) }}</p>
                    <p>{{ 'plugins/loyalty-points::email-templates.points_redeemed_message' | trans({'points': points_redeemed, 'discount': discount_amount, 'order_code': order_code}) }}</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-pt-0">
                    <table class="bb-table" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.points_redeemed_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">
                                    <span style="color: #dc3545; font-weight: bold;">-{{ points_redeemed }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.discount_amount_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ discount_amount }}</td>
                            </tr>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.order_code_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ order_code }}</td>
                            </tr>
                            <tr>
                                <td class="bb-p-sm">
                                    <strong>{{ 'plugins/loyalty-points::email-templates.remaining_balance_label' | trans }}</strong>
                                </td>
                                <td class="bb-p-sm bb-text-right">{{ remaining_balance }} {{ 'plugins/loyalty-points::email-templates.points' | trans }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
                    <p>{{ 'plugins/loyalty-points::email-templates.points_redeemed_cta' | trans }}</p>
                    <table cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" class="bb-bg-blue bb-rounded bb-w-auto">
                                        <tr>
                                            <td align="center" valign="top" class="lh-1">
                                                <a href="{{ site_url }}" class="bb-btn bb-bg-blue bb-border-blue">
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
