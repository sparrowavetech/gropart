# Deployment & Functional Test Checklist

## Update

- Back up database and `platform/plugins/india-sms-gateway`.
- Overwrite plugin files.
- Run `php artisan migrate --force`.
- Run `php artisan optimize:clear`, `route:clear` and `view:clear`.
- Confirm the plugin remains active.

## Gateway

- Configure and enable one gateway.
- Enable India SMS service.
- Select the configured gateway as default.
- Send English test SMS.
- Send Bangla/Unicode test SMS.
- Confirm both entries appear in Delivery Logs.

## OTP

- Enable OTP service.
- Open customer registration page and confirm the verification notice appears.
- Submit registration and verify the OTP modal opens.
- Confirm a wrong OTP is rejected and the attempt counter is enforced.
- Confirm registration succeeds after the correct OTP.
- Open login and confirm “Login with OTP” appears.
- Test OTP login with an existing customer phone.
- Open forgot-password and test reset with mobile OTP.
- Enable checkout OTP and confirm an order cannot be submitted without verification.

## Ecommerce SMS

- Enter admin mobile number(s) in settings.
- Enable admin new-order SMS.
- Enable customer new-order SMS.
- Enable customer status-change SMS.
- Place a test order with a valid customer/shipping phone.
- Confirm the admin and customer receive their configured new-order messages.
- Change order status to Processing and confirm the customer SMS.
- Change another status and confirm the correct status-specific or fallback template.
- Check Delivery Logs for gateway response and failures.

## Queue

- Leave “queue worker confirmed” unchecked unless a persistent worker is running.
- When queueing is enabled, run `php artisan queue:work --queue=sms,default`.
