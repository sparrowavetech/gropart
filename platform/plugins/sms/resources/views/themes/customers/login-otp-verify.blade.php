@php
    Theme::layout('full-width');
@endphp

{!! Theme::partial('page-header', ['withTitle' => false, 'size' => 'xl']) !!}

<div class="container">
    <div class="row customer-auth-page py-md-5 mt-md-5 justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5">
            <div class="customer-auth-form bg-light p-4">
                <h1 class="h4 fw-bold mb-3">{{ __('Verify Login OTP') }}</h1>

                @if (session('success_msg'))
                    <div class="alert alert-success">{{ session('success_msg') }}</div>
                @endif

                @if ($errors->has('confirmation'))
                    <div class="alert alert-danger">{{ $errors->first('confirmation') }}</div>
                @endif

                <form method="POST" action="{{ route('customer.login.otp.verify.post') }}">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->getKey() }}">

                    <div class="mb-3">
                        <label class="form-label" for="otp">{{ __('OTP') }}</label>
                        <input
                            id="otp"
                            class="form-control @if ($errors->has('otp')) is-invalid @endif"
                            type="text"
                            name="otp"
                            maxlength="6"
                            placeholder="{{ __('Enter OTP') }}"
                        >
                        @if ($errors->has('otp'))
                            <div class="invalid-feedback">{{ $errors->first('otp') }}</div>
                        @endif
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit">{{ __('Verify & Login') }}</button>
                    </div>
                </form>

                <div class="mt-3">
                    <form method="POST" action="{{ route('customer.login.otp.send') }}">
                        @csrf
                        <input type="hidden" name="phone" value="{{ $customer->phone }}">
                        <button class="btn btn-link p-0" type="submit">{{ __('Resend OTP') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
