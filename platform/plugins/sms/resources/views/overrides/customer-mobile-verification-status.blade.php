@php
    $mobileVerified = ! empty($customer->sms_otp_verified_at);
    $mobilePending = ! $mobileVerified && ! empty($customer->sms_otp_required_at);
    $mobileClass = $mobileVerified ? 'is-verified' : ($mobilePending ? 'is-pending' : 'is-unknown');
    $mobileLabel = $mobileVerified ? 'otp_verified_yes' : ($mobilePending ? 'otp_pending' : 'otp_not_recorded');
@endphp

<div class="sms-customer-verification-row">
    <span class="sms-customer-verification-label">{{ trans('plugins/sms::sms.mobile_otp_short') }}</span>
    <span class="sms-customer-verification-state {{ $mobileClass }}">{{ trans('plugins/sms::sms.' . $mobileLabel) }}</span>
</div>

<style>
    .sms-customer-verification { max-width: 16rem; margin: 1rem auto 0; text-align: left; }
    .sms-customer-verification-row { display: flex; align-items: center; justify-content: space-between; gap: .65rem; padding: .35rem 0; font-size: .8125rem; line-height: 1.25; }
    .sms-customer-verification-row + .sms-customer-verification-row { border-top: 1px solid var(--bb-border-color, #e4e9ef); }
    .sms-customer-verification-label { color: var(--bb-secondary-color, #667085); font-weight: 500; }
    .sms-customer-verification-state { display: inline-block; flex: none; border-radius: .35rem; padding: .25rem .5rem; font-size: .75rem; font-weight: 600; white-space: nowrap; }
    .sms-customer-verification-state.is-verified { background: #e5f6ec; color: #146c43; }
    .sms-customer-verification-state.is-pending { background: #fff2d2; color: #895900; }
    .sms-customer-verification-state.is-unknown { background: #eef1f5; color: #596579; }
</style>
