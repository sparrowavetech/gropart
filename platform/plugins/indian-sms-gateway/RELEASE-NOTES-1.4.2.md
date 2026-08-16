# Indian SMS Gateway 1.4.2

## Fast2SMS DLT fixes

- Uses the Fast2SMS `GET /dev/bulkV2` request format.
- Sends the provider Message ID in `message` for DLT routes.
- Builds `variables_values` in approved-template placeholder order.
- Requires a mapped Fast2SMS Message ID when route is `dlt`.
- Keeps direct-message mode available for non-DLT routes.
- Preserves Sender ID exactly as saved, except leading/trailing spaces.
- Adds dynamic variable inputs and live preview to Test SMS.

## Required Fast2SMS settings

- Sender ID: the approved header, for example `GROPRT`.
- Route: `dlt` for approved DLT templates.
- Endpoint: `https://www.fast2sms.com/dev/bulkV2`.
- Template mapping: add the Fast2SMS Message ID, such as `222071`, to the corresponding message template.
