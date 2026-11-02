@php
    Theme::layout('default');
@endphp

<div class="container">
    <div class="row customer-auth-page py-md-5 mt-md-5 justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5">
            <div class="customer-auth-form bg-light p-4">
                <h1 class="h4 fw-bold mb-3">{{ trans('plugins/sms::sms.verify_login_otp') }}</h1>

                @if (session('success_msg'))
                    <div class="alert alert-success">{{ session('success_msg') }}</div>
                @endif

                @if ($errors->has('confirmation') || $errors->has('phone'))
                    <div class="alert alert-danger">{{ $errors->first('confirmation') ?: $errors->first('phone') }}</div>
                @endif

                <form method="POST" action="{{ route('customer.login.otp.verify.post') }}">
                    @csrf

                    @include('plugins/sms::themes.customers.partials.otp-input')

                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit">{{ trans('plugins/sms::sms.verify_and_login') }}</button>
                    </div>
                </form>

                <div class="mt-3">
                    <form method="POST" action="{{ route('customer.login.otp.send') }}">
                        @csrf
                        <input type="hidden" name="phone" value="{{ $customer->phone }}">
                        <button class="btn btn-link p-0" type="submit">{{ trans('plugins/sms::sms.resend_otp') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
