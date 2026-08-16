# Changelog

## 1.4.3
- Fast2SMS DLT test form now shows only templates mapped with a Fast2SMS Message ID.
- Automatically selects the first valid mapped template.
- Prevents submitting an unmapped template and links directly to template configuration.
- Clarifies DLT/direct-mode guidance and preserves all v1.4.2 fixes.

## 1.4.2

- Rebuilt Fast2SMS DLT test flow around provider Message IDs and ordered variables.
- Fast2SMS now uses the documented GET bulkV2 request format.
- DLT route requires a mapped template; direct routes remain message-based.
- Added dynamic test-variable inputs and payload-aware validation.
- Sender IDs are preserved exactly except for leading/trailing spaces.

# Changelog

## 1.4.1
- Removed fixed six-character Sender ID validation from Fast2SMS.
- Sender IDs are now passed using the exact configured provider-approved value, with surrounding whitespace trimmed only.
- Removed provider-specific Sender ID length checks from diagnostics.
- Updated gateway help text to remain provider-neutral.

## 1.4.0
- Added gateway configuration health diagnostics and readiness score.
- Added safe request preview with sender, entity, route and template mode.
- Added Fast2SMS sender ID validation and automatic uppercase/space normalization.
- Improved route and sender guidance in gateway settings.
- Preserved per-template DLT mapping, OTP flows, email validation and optional direct-message mode.

## 1.3.0

- Rebuilt the gateway test view to remove Blade parsing conflicts caused by JavaScript template-variable syntax.
- Added safe live-preview variable rendering with defined sample data.
- Kept DLT/template IDs optional at plugin level for every gateway.
- Added gateway-specific helper text and clear direct-message/mapped-ID status.
- Preserved template mapping, registration OTP, email remote validation, and custom template persistence fixes.

## 1.2.8

- Made DLT / provider Template IDs optional at plugin level.
- Removed Fast2SMS-only helper copy and forced template validation from test SMS.
- Added direct-message fallback when no provider-specific template ID is mapped.
- Added a unique versioned gateway view to prevent stale Blade UI from loading.
- Updated gateway-aware helper/status text for mapped and direct-message modes.

## 1.2.7

- Redesigned Gateway → Template → Test SMS workflow.
- Added gateway-specific dynamic DLT guidance and template filtering.
- Added mapped-template counts to gateway cards.
- Added live rendered-message preview with sample variable replacement.
- Added provider mapping matrix on the Templates page.
- Added generic server-side mapping validation for selected gateway/template pairs.

# Changelog

## 1.2.6
- Fixed customized message content being overwritten by default seed data.
- Template defaults are now inserted only when missing.
- Added transactional template saving and database readiness checks.
- Simplified template editor with optional gateway mapping accordion.
- Locked system template keys during editing to prevent broken event mappings.

## 1.2.6
- Fixed Fast2SMS DLT test requests so the selected template ID is sent as `message`.
- Sends only ordered DLT variable values in `variables_values` instead of the full rendered message.
- Added approved-template selection and clear validation to the Test SMS form.
- Registration/checkout OTP now passes named template variables to provider drivers.
- Returns a local configuration error before calling Fast2SMS when the selected template has no Fast2SMS DLT ID.

## 1.2.3

- Enforces registration and checkout OTP through Laravel's web middleware group, including custom Botble themes.
- Injects the OTP frontend UI into HTML responses even when a theme does not fire the standard footer hook.
- Broadens registration-form detection for custom registration layouts.
- Prevents customer creation until registration OTP verification succeeds.
- Passes customer name to registration OTP templates.
- Supports Indian DLT `{#VAR#}` placeholders in approved message text.
- Keeps OTP failures visible in the OTP and SMS logs.

## 1.2.2
- Moved DLT template IDs from gateway-wide settings to each message template.
- Added separate DLT/template and Sender ID mapping for every gateway.
- Fixed template fields not persisting during edit.
- Added backward compatibility for legacy gateway-level template IDs.

# Changelog

## 1.2.1 - MySQL timestamp compatibility fix

- Replaced custom `timestamp` columns with `dateTime` columns in the installer and migrations.
- Fixes `Invalid default value for expires_at` on MySQL/MariaDB servers using restrictive timestamp defaults.
- Keeps nullable dates, indexes, OTP expiry logic, and existing plugin behavior unchanged.


## 1.2.0
- Renumbered the heartbeat-enabled release as version 1.2.0.
- Reports version 1.2.0 to Ashikul License Manager.
- Preserved the complete 100-SMS trial, license integration, gateway, OTP and ecommerce features.
- Includes non-blocking installation heartbeat reporting for the Installed Websites dashboard.

## 1.0.10
- Fixed fresh installations incorrectly showing the 100-SMS trial as exhausted.
- Added an installation-specific trial baseline.
- Existing SMS logs from earlier plugin versions no longer consume the new trial.
- Automatically repairs persisted `trial_limit=0` settings to 100.
- Stores trial start time and baseline accepted-message count.
- Trial usage now counts only accepted SMS sent after the current trial began.
- Preserved the full plugin and Ashikul License Panel integration.

## 1.0.9
- Restored the full 100-SMS free trial inside the functional Indian SMS plugin.
- Full plugin features are available while the trial has remaining SMS.
- Only successfully accepted, sent or delivered SMS count toward the trial.
- Failed gateway requests do not consume the trial.
- Trial usage is calculated from delivery logs to avoid counter mismatch.
- Sending, OTP, order alerts and test SMS are locked after the 100th accepted SMS until activation.
- Added trial progress, used count and remaining count to the License page.
- Preserved Ashikul License Panel activation, validation, deactivation and single-domain binding.

## 1.0.8
- Restored the complete Indian SMS dashboard and all functional menus.
- License is now integrated inside the full plugin instead of replacing it.
- Overview, Logs, Templates, OTP, Gateways, Admin SMS, Settings and License remain visible.
- Protected features redirect to License only when activation is missing.
- Removed incorrect free-trial copy from the remote license screen.
- Retained all v1.0.5 functional SMS, OTP, ecommerce, gateway and database features.
- Retained Ashikul License Panel activation, validation, deactivation and domain binding.

## 1.0.7
- Fixed `Undefined array key "trial_limit"` on the activation page.
- Added backward-compatible trial status keys for old compiled Blade views.
- Made all activation-view license fields null-safe.
- Preserved Ashikul License Panel activation, validation and deactivation.
- Updated the plugin version sent to the license server.
- Added safe defaults for legacy trial settings.

## 1.0.6
- Connected activation to Ashikul License Panel.
- Added remote activate, validate, refresh and deactivate operations.
- Added single-domain server enforcement and stable installation ID.
- Added encrypted local key storage, 12-hour validation cache and 72-hour offline grace.
- Removed dependency on embedded local license-key hashes.

## 1.0.5
- Added a 100-successful-SMS free trial before license activation.
- Kept all admin features available during the trial.
- Added trial usage and remaining-message status to the License page.
- Added sending and dashboard lock after the trial limit until activation.

## 1.0.4
- Simplified Local Indian Gateway for non-technical users.
- Moved field mapping and response detection into collapsed Advanced settings.
- Added clear required fields, sensible defaults and request preview.
- Changed DCS and Flash SMS to simple dropdowns.
- Added guaranteed inline SVG icons to dashboard statistics.

## 1.0.3
- Added database self-repair and missing-table guards.
