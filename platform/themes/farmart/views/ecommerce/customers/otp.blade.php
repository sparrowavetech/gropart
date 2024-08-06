@php Theme::layout('full-width'); @endphp

{!! Theme::partial('page-header', ['withTitle' => false, 'size' => 'xl']) !!}
<style >
    .changePhoneFrom{
        display:none;
    }

</style>
<script>
    function changePhone(){
            $('.changePhoneFrom').show()
            $('.otpFrom').hide()
    }
</script>
<div class="container">
    <div class="row customer-auth-page py-md-5 mt-md-5 justify-content-center">
        <div class="col-sm-9 col-md-6 col-lg-5 col-xl-4">
            <div class="customer-auth-form bg-light pt-1 py-3 px-4">
                <nav>
                    <div class="nav nav-tabs">
                        <h1 class="nav-link fs-5 fw-bold">{{ __('OTP Verification') }}</h1>
                    </div>
                </nav>
                <div class="tab-content my-3">
                    <div class="tab-pane fade  show active" id="nav-login-content" role="tabpanel"
                        aria-labelledby="nav-home-tab">
                        @if (isset($errors) && $errors->has('confirmation'))
                            <div class="alert alert-danger">
                                <span>{!! BaseHelper::clean($errors->first('confirmation')) !!}</span>
                            </div>
                        @else
                        <div class="alert alert-success">
                                <span>{{ __('One Time Password send to your no ') }} {{ $customer->phone}} <a href="javascript:;" onclick='changePhone()'>Change Phone</a></span>
                            </div>
                        @endif
                        <form class="mt-3 otpFrom" method="POST" action="{{ route('customer.otp.post') }}" >
                            @csrf
                          <input type="hidden" value="{{ $customer_id }}" name="customer_id">
                          <div class="input-group mb-3 input-group-with-text">
                                <input class="form-control @if ($errors->has('otp')) is-invalid @endif" type="text" placeholder="{{ __('OTP') }}"
                                    aria-label="{{ __('OTP') }}" maxlength="6" name="otp">
                                @if ($errors->has('otp'))
                                    <div class="invalid-feedback">{{ $errors->first('otp') }}</div>
                                @endif
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-primary" type="submit">{{ __('Verify') }}</button>
                            </div>
                            <div class="mt-3">
                                 <a href="{{ route('customer.resend',$customer_id) }}" class="d-inline-block text-primary">{{ __('Resent OTP') }}</a></p>
                            </div>
                        </form>
                        <form class="mt-3 changePhoneFrom" method="POST" action="{{ route('customer.otp.changePhone') }}">
                            @csrf
                          <input type="hidden" value="{{ $customer_id }}" name="customer_id">
                          <div class="input-group mb-3 input-group-with-text " id="phonediv">
                                <input class="form-control @if ($errors->has('phone')) is-invalid @endif" type="text" placeholder="{{ __('Phone No') }}"
                                    aria-label="{{ __('Phone') }}"  value="{{  $customer->phone }}" name="phone">
                                @if ($errors->has('phone'))
                                    <div class="invalid-feedback">{{ $errors->first('phone') }}</div>
                                @endif
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-primary changephone" type="submit">{{ __('Send OTP') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
