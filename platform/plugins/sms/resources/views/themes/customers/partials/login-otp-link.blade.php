<div class="sms-login-otp-option mt-3">
    <div class="d-flex align-items-center gap-2 my-3 text-muted">
        <span class="border-top flex-grow-1"></span>
        <span>{{ __('or') }}</span>
        <span class="border-top flex-grow-1"></span>
    </div>
    <a href="{{ route('customer.login.otp') }}" class="btn btn-outline-primary w-100">
        <i class="icon-phone me-1"></i>
        {{ __('Login with OTP') }}
    </a>
</div>
