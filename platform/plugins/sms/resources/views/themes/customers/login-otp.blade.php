@php
    Theme::layout('full-width');
@endphp

{!! Theme::partial('page-header', ['withTitle' => false, 'size' => 'xl']) !!}

<div class="container">
    <div class="row customer-auth-page py-md-5 mt-md-5 justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5">
            <div class="customer-auth-form bg-light p-4">
                <h1 class="h4 fw-bold mb-3">{{ __('Login with OTP') }}</h1>

                <form method="POST" action="{{ route('customer.login.otp.send') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="phone">{{ __('Enter mobile number') }}</label>
                        <input
                            id="phone"
                            class="form-control js-phone-number-mask @if ($errors->has('phone')) is-invalid @endif"
                            type="tel"
                            name="phone_display"
                            value="{{ old('phone_display', old('phone')) }}"
                            placeholder="{{ __('Mobile number') }}"
                            data-country-code-selection="true"
                            autocomplete="tel"
                        >
                        <input
                            type="hidden"
                            name="phone"
                            id="phone-full"
                            class="js-phone-number-full"
                            data-phone-field="phone_display"
                            value="{{ old('phone') }}"
                        >
                        @if ($errors->has('phone'))
                            <div class="invalid-feedback">{{ $errors->first('phone') }}</div>
                        @endif
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit">{{ __('Send OTP') }}</button>
                    </div>
                </form>

                <div class="mt-3 text-center">
                    <a href="{{ route('customer.login') }}" class="fw-bold text-decoration-underline">
                        <i class="icon-lock me-1"></i>{{ __('Login with password') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@include('core/base::forms.fields.phone-number-script')
