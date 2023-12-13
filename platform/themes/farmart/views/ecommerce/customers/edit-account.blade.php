@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')
@section('content')
    {!! Form::open(['route' => 'customer.edit-account', 'method' => 'POST']) !!}
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a
                class="nav-link active"
                data-toggle="tab"
                href="#tab_profile"
            >{{ SeoHelper::getTitle() }} </a>
        </li>
        {!! apply_filters(BASE_FILTER_REGISTER_CONTENT_TABS, null, auth('customer')->user()) !!}
    </ul>
    <div class="tab-content px-2 py-4 border border-top-0">
        <div
            class="tab-pane active"
            id="tab_profile"
        >
            <div class="form-content">
                <div class="mb-3">
                    <label class="form-label" for="name">{{ __('Full Name') }}:</label>
                    <input
                        class="form-control @if ($errors->has('name')) is-invalid @endif"
                        id="name"
                        name="name"
                        type="text"
                        value="{{ auth('customer')->user()->name }}"
                    >
                    {!! Form::error('name', $errors) !!}
                </div>

                <div class="mb-3">
                    <label class="form-label" for="date_of_birth">{{ __('Date of birth') }}:</label>
                    <input
                        class="form-control @if ($errors->has('dob')) is-invalid @endif"
                        id="date_of_birth"
                        name="dob"
                        type="text"
                        value="{{ auth('customer')->user()->dob }}"
                    >
                    {!! Form::error('dob', $errors) !!}
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">{{ __('Email') }}:</label>
                    <input
                        class="form-control"
                        id="email"
                        name="email"
                        type="email"
                        value="{{ auth('customer')->user()->email }}"
                        disabled="disabled"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="phone">{{ __('Phone') }}</label>
                    <input
                        class="form-control @if ($errors->has('phone')) is-invalid @endif"
                        id="phone"
                        name="phone"
                        type="text"
                        value="{{ auth('customer')->user()->phone }}"
                        placeholder="{{ __('Phone') }}"
                    >
                    {!! Form::error('phone', $errors) !!}
                </div>
            </div>
        </div>
        {!! apply_filters(BASE_FILTER_REGISTER_CONTENT_TAB_INSIDE, null, auth('customer')->user()) !!}
    </div>
    <div class="my-3">
        <button class="btn btn-primary">{{ __('Update') }}</button>
    </div>
    {!! Form::close() !!}
@endsection
