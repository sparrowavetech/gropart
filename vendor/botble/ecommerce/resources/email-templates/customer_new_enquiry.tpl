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
                                <img src="{{ 'shopping-cart' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon" />
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">Enquiry successfully!</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content">
                    <p>Dear {{ customer_name }},</p>
                    <div>Thank you for making your enquiry on our products, we will contact you regarding your product enquiry via provided contact number <strong>{{ customer_phone }}</strong> shortly!</div>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-pt-0">
                    <table class="bb-row bb-mb-md" cellpadding="0" cellspacing="0">
                        <tbody>
                            <tr>
                                <td class="bb-bb-col">
                                    <h4 class="bb-m-0">Customer Information</h4>
                                    <div>Name: <strong>{{ customer_name }}</strong></div>
                                    {% if customer_phone %}
                                        <div>Phone: <strong>{{ customer_phone }}</strong></div>
                                    {% endif %}
                                    {% if customer_email %}
                                        <div>Email: <strong>{{ customer_email }}</strong></div>
                                    {% endif %}
                                    {% if customer_address %}
                                        <div>Address: <strong>{{ customer_address }}</strong></div>
                                    {% endif %}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-pt-0">
                    <h4>Here's what you enquired:</h4>
                    {{ product_list }}
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{ footer }}

