# Botble Marketplace Submission Notes

## Package identity

- Package ID: `ashikul/india-sms-gateway`
- URL slug: `ashikul/india-sms-gateway`
- Product name: **Indian SMS**
- Product type: Plugin
- Category: CMS / Ecommerce
- Author: Ashikul Islam
- Author website: https://ashikul.info
- Minimum Botble version: 7.6.8
- Required plugin: Ecommerce
- License: Commercial / Proprietary, single production domain
- Activation key price: US$10

## Suggested short description

Connect India SMS gateways to Botble CMS for OTP verification, customer and admin order alerts, status-change notifications, delivery logs, templates and gateway fallback.

## Suggested tags

`botble,sms,india,otp,ecommerce,order-notification,bulksmsbd,mimsms,alpha-sms`

## Suggested dependencies field

`botble/ecommerce`

## ZIP validation

The package keeps `plugin.json` at the ZIP root. Its `id`, `name`, `namespace`,
`provider`, `author`, semantic `version`, and minimum Botble version are included.

## Suggested screenshots

1. Overview and system health
2. Gateway configuration
3. Test SMS success and delivery log
4. OTP and verification settings
5. Customer/admin order notification settings
6. Activation and support page

## Review notes

- No API credentials are bundled.
- License keys are validated locally against hashes; raw keys are not distributed inside the plugin.
- The plugin does not modify Botble core files.
- Admin UI uses Botble-compatible views and navigation.
