@php Theme::layout('full-width'); @endphp

{!! Theme::partial('page-header', ['size' => 'xl', 'withTitle' => true]) !!}

<div class="container">
    <div class="row reset-password-page py-5 mt-3 justify-content-center">
        <div class="col-sm-6">
            <div class="reset-password-form bg-light p-4">
                <form
                    class="mt-3"
                    method="POST"
                    action="{{ route('customer.password.reset.post') }}"
                >
                    @csrf
                    <input
                        name="token"
                        type="hidden"
                        value="{{ $token }}"
                    />
                    <div class="mb-3">
                        <input
                            class="form-control @if ($errors->has('email')) is-invalid @endif"
                            name="email"
                            type="text"
                            value="{{ old('email') }}"
                            required=""
                            placeholder="{{ __('Email address') }}"
                            autocomplete="email"
                        >
                        @if ($errors->has('email'))
                            <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <input
                            class="form-control @if ($errors->has('email')) is-invalid @endif"
                            name="password"
                            type="password"
                            value="{{ old('email') }}"
                            required=""
                            placeholder="{{ __('Password') }}"
                        >
                        @if ($errors->has('password'))
                            <span class="invalid-feedback">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <input
                            class="form-control @if ($errors->has('email')) is-invalid @endif"
                            name="password_confirmation"
                            type="password"
                            value="{{ old('email') }}"
                            required=""
                            placeholder="{{ __('Password confirmation') }}"
                        >
                        @if ($errors->has('password_confirmation'))
                            <span class="invalid-feedback">{{ $errors->first('password_confirmation') }}</span>
                        @endif
                    </div>
                    <div class="d-grid">
                        <button
                            class="btn btn-primary"
                            type="submit"
                        >{{ __('Submit') }}</button>
                    </div>
                </form>

                @if (session('status'))
                    <div class="text-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('success_msg'))
                    <div class="text-success">
                        {{ session('success_msg') }}
                    </div>
                @endif

                @if (session('error_msg'))
                    <div class="text-danger">
                        {{ session('error_msg') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
