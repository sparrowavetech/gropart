@php
    Theme::layout('default');
@endphp

<div class="container">
    <div class="row customer-auth-page py-md-5 mt-md-5 justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5">
            <div class="customer-auth-form bg-light p-4">
                <h1 class="h4 fw-bold mb-3">{{ trans('plugins/sms::sms.otp_page_title') }}</h1>

                @if (session('error_msg'))
                    <div class="alert alert-danger">{{ session('error_msg') }}</div>
                @elseif (isset($errors) && $errors->has('confirmation'))
                    <div class="alert alert-danger">
                        {!! BaseHelper::clean($errors->first('confirmation')) !!}
                    </div>
                @else
                    <div class="alert alert-success">
                        {{ session('success_msg') ?: trans('plugins/sms::sms.otp_sent_to') . ' ' . $customer->phone }}
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.otp.post') }}" class="mb-3">
                    @csrf

                    @include('plugins/sms::themes.customers.partials.otp-input')

                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit">{{ trans('plugins/sms::sms.verify') }}</button>
                    </div>
                </form>

                <div class="d-flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('customer.resend') }}">@csrf
                        <button class="btn btn-link p-0" type="submit">{{ trans('plugins/sms::sms.resend_otp') }}</button>
                    </form>
                    <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#change-phone-form">
                        {{ trans('plugins/sms::sms.change_phone') }}
                    </button>
                </div>

                <div class="collapse mt-3" id="change-phone-form">
                    <form method="POST" action="{{ route('customer.otp.changePhone') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="phone">{{ trans('plugins/sms::sms.phone_number') }}</label>
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

                        <button class="btn btn-primary" type="submit">{{ trans('plugins/sms::sms.send_otp') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
