@extends(EcommerceHelper::viewPath('customers.master'))

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2 class="customer-page-title">{{ __('Account information') }}</h2>
        </div>
        <div class="panel-body">
            {!! Form::open(['route' => 'customer.edit-account']) !!}
                <div class="form-group mb-3">
                    <label
                        class="form-label"
                        for="name"
                    >{{ __('Full Name') }}: </label>
                    <input
                        class="form-control"
                        id="name"
                        name="name"
                        type="text"
                        value="{{ auth('customer')->user()->name }}"
                    >
                    {!! Form::error('name', $errors) !!}
                </div>

                <div class="form-group mb-3 @if ($errors->has('dob')) has-error @endif">
                    <label
                        class="form-label"
                        for="date_of_birth"
                    >{{ __('Date of birth') }}: </label>
                    <input
                        class="form-control"
                        id="date_of_birth"
                        name="dob"
                        type="text"
                        value="{{ auth('customer')->user()->dob }}"
                    >
                    {!! Form::error('dob', $errors) !!}
                </div>
                <div class="form-group mb-3 @if ($errors->has('email')) has-error @endif">
                    <label
                        class="form-label"
                        for="email"
                    >{{ __('Email') }}: </label>
                    <input
                        class="form-control"
                        id="email"
                        name="email"
                        type="text"
                        value="{{ auth('customer')->user()->email }}"
                        disabled="disabled"
                    >
                    {!! Form::error('email', $errors) !!}
                </div>

                <div class="form-group mb-3 @if ($errors->has('phone')) has-error @endif">
                    <label
                        class="form-label"
                        for="phone"
                    >{{ __('Phone') }}: </label>
                    <input
                        class="form-control"
                        id="phone"
                        name="phone"
                        type="text"
                        value="{{ auth('customer')->user()->phone }}"
                        placeholder="{{ __('Phone') }}"
                    >
                    {!! Form::error('phone', $errors) !!}
                </div>

                <div class="form-group">
                    <button
                        class="btn btn-primary"
                        type="submit"
                    >{{ __('Update') }}</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
