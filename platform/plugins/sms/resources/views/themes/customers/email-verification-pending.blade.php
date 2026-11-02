@php
    Theme::layout('default');
@endphp

<div class="container">
    <div class="row customer-auth-page py-md-5 mt-md-5 justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5">
            <div class="customer-auth-form bg-light p-4 text-center">
                <h1 class="h4 fw-bold mb-3">{{ trans('plugins/sms::sms.email_pending_title') }}</h1>
                <p class="mb-0">{{ trans('plugins/sms::sms.email_pending_message') }}</p>
            </div>
        </div>
    </div>
</div>
