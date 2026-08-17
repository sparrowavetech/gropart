@php
    Theme::layout('full-width');
@endphp

{!! Theme::partial('page-header', ['withTitle' => false, 'size' => 'xl']) !!}

<div class="container">
    <div class="row customer-auth-page py-md-5 mt-md-5 justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5">
            <div class="customer-auth-form bg-light p-4">
                <h1 class="h4 fw-bold mb-3">{{ __('OTP Verification') }}</h1>

                @if (isset($errors) && $errors->has('confirmation'))
                    <div class="alert alert-danger">
                        {!! BaseHelper::clean($errors->first('confirmation')) !!}
                    </div>
                @else
                    <div class="alert alert-success">
                        {{ __('One Time Password sent to') }} {{ $customer->phone }}
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.otp.post') }}" class="mb-3">
                    @csrf
                    <input type="hidden" value="{{ $customer_id }}" name="customer_id">

                    <div class="mb-3">
                        <label class="form-label" for="otp">{{ __('OTP') }}</label>
                        <input
                            id="otp"
                            class="form-control @if ($errors->has('otp')) is-invalid @endif"
                            type="text"
                            placeholder="{{ __('Enter OTP') }}"
                            maxlength="6"
                            name="otp"
                        >
                        @if ($errors->has('otp'))
                            <div class="invalid-feedback">{{ $errors->first('otp') }}</div>
                        @endif
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit">{{ __('Verify') }}</button>
                    </div>
                </form>

                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('customer.resend', $customer_id) }}">{{ __('Resend OTP') }}</a>
                    <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#change-phone-form">
                        {{ __('Change Phone') }}
                    </button>
                </div>

                <div class="collapse mt-3" id="change-phone-form">
                    <form method="POST" action="{{ route('customer.otp.changePhone') }}">
                        @csrf
                        <input type="hidden" value="{{ $customer_id }}" name="customer_id">

                        <div class="mb-3">
                            <label class="form-label" for="phone">{{ __('Phone No') }}</label>
                            <input
                                id="phone"
                                class="form-control @if ($errors->has('phone')) is-invalid @endif"
                                type="text"
                                value="{{ $customer->phone }}"
                                name="phone"
                            >
                            @if ($errors->has('phone'))
                                <div class="invalid-feedback">{{ $errors->first('phone') }}</div>
                            @endif
                        </div>

                        <button class="btn btn-primary" type="submit">{{ __('Send OTP') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
