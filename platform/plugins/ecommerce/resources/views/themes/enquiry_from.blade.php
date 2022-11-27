{!! Html::script('vendor/core/plugins/ecommerce/js/checkout.js?v=1.2.0') !!}

@if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
<script src="{{ asset('vendor/core/plugins/location/js/location.js') }}?v=1.2.0"></script>
@endif
{!! Form::open(['url' =>route('public.enquiry.form'), 'method' => 'POST','enctype'=>'multipart/form-data']) !!}
<input type="hidden" name="product_id" value="{{ $product->id}}">

<div class="card" style="width: 18rem;">
    <img class="card-img-top lazyload product-thumbnail__img" src="{{ image_placeholder($product->image, 'small') }}" data-src="{{ RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}">
    <div class="card-body">
        <p class="card-text">{{ $product->name }}</p>
    </div>
</div>
<div class="form-content">
    <div class="mb-3">
        <label for="name">{{ __('Full Name') }}:</label>
        <input id="name" type="text" class="form-control @if ($errors->has('name')) is-invalid @endif" name="name" value="{{ auth()->user()->name }}" placeholder="{{ __('Enter Full Name') }}" required minlength="3" maxlength="120">
        @if ($errors->has('name'))
        <div class="invalid-feedback">
            {{ $errors->first('name') }}
        </div>
        @endif
    </div>

    <div class="mb-3">
        <label for="email">{{ __('Email') }}:</label>
        <input id="email" type="email" class="form-control @if ($errors->has('email')) is-invalid @endif" name="email" value="{{ auth()->user()->email }}" placeholder="{{ __('Enter Email') }}" required>
        @if ($errors->has('email'))
        <div class="invalid-feedback">
            {{ $errors->first('email') }}
        </div>
        @endif
    </div>

    <div class="mb-3">
        <label for="phone">{{ __('Phone:') }}</label>
        <input id="phone" type="text" class="form-control @if ($errors->has('phone')) is-invalid @endif" name="phone" value="{{ auth()->user()->phone }}" placeholder="{{ __('Enter Phone') }}" required>
        @if ($errors->has('phone'))
        <div class="invalid-feedback">
            {{ $errors->first('phone') }}
        </div>
        @endif
    </div>

    <div class="mb-3">
        @if (EcommerceHelper::isUsingInMultipleCountries())
        <label for="country">{{ __('Country') }}:</label>
        <select name="country" class="form-select @if ($errors->has('state')) is-invalid @endif" id="country" data-type="country" required>
            @foreach(EcommerceHelper::getAvailableCountries() as $countryCode => $countryName)
            <option value="{{ $countryCode }}" @if (auth()->user()->country == $countryCode) selected @endif>{{ $countryName }}</option>
            @endforeach
        </select>
        @else
        <input type="hidden" name="country" value="{{ EcommerceHelper::getFirstCountryId() }}">
        @endif
        @if ($errors->has('country'))
        <div class="invalid-feedback">
            {{ $errors->first('country') }}
        </div>
        @endif
    </div>

    <div class="mb-3">
        <label for="state">{{ __('State') }}:</label>
        @if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
        <select name="state" class="form-select @if ($errors->has('state')) is-invalid @endif" id="state" data-type="state" data-placeholder="{{ __('Select state...') }}" data-url="{{ route('ajax.states-by-country') }}">
            <option value="">{{ __('Select state...') }}</option>
            @if (old('country', auth()->user()->country) || !EcommerceHelper::isUsingInMultipleCountries())
            @foreach(EcommerceHelper::getAvailableStatesByCountry(old('country', auth()->user()->country)) as $stateId => $stateName)
            <option value="{{ $stateId }}" @if (old('state', auth()->user()->state) == $stateId) selected @endif>{{ $stateName }}</option>
            @endforeach
            @endif
        </select>
        @else
        <input id="state" type="text" class="form-control @if ($errors->has('state')) is-invalid @endif" name="state" value="{{ $address->state }}" placeholder="{{ __('Enter State') }}" required>
        @endif
        @if ($errors->has('state'))
        <div class="invalid-feedback">
            {{ $errors->first('state') }}
        </div>
        @endif
    </div>

    <div class="mb-3">
        <label for="city">{{ __('City') }}:</label>
        @if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
        <select name="city" class="form-select @if ($errors->has('city')) is-invalid @endif" id="city" data-type="city" data-placeholder="{{ __('Select city...') }}" data-url="{{ route('ajax.cities-by-state') }}">
            <option value="">{{ __('Select city...') }}</option>
            @if (old('state', auth()->user()->state))
            @foreach(EcommerceHelper::getAvailableCitiesByState(old('state', auth()->user()->state)) as $cityId => $cityName)
            <option value="{{ $cityId }}" @if (old('city', auth()->user()->city) == $cityId) selected @endif>{{ $cityName }}</option>
            @endforeach
            @endif
        </select>
        @else
        <input id="city" type="text" class="form-control" name="city" value="{{ auth()->user()->city }}" placeholder="{{ __('Enter City') }}">
        @endif
        @if ($errors->has('city'))
        <div class="invalid-feedback">
            {{ $errors->first('city') }}
        </div>
        @endif
    </div>

    <div class="mb-3">
        <label for="address">{{ __('Address') }}:</label>
        <input id="address" type="text" class="form-control @if ($errors->has('address')) is-invalid @endif" name="address" value="{{auth()->user()->address }}" placeholder="{{ __('Enter Address') }}">
        @if ($errors->has('address'))
        <div class="invalid-feedback">
            {{ $errors->first('address') }}
        </div>
        @endif
    </div>

    @if (EcommerceHelper::isZipCodeEnabled())
    <div class="mb-3">
        <label>{{ __('Zip code') }}:</label>
        <input id="zip_code" type="text" class="form-control @if ($errors->has('zip_code')) is-invalid @endif" name="zip_code" value="{{ auth()->user()->zip_code }}" placeholder="{{ __('Enter Zip code') }}">
        @if ($errors->has('zip_code'))
        <div class="invalid-feedback">
            {{ $errors->first('zip_code') }}
        </div>
        @endif
    </div>
    @endif
    <div class="mb-3">
        <label for="description">{{ __('Description') }}:</label>
        <textarea name="description" id="" cols="30" rows="10" class="form-control" name="description" placeholder="{{ __('Additional Requirement') }}"></textarea>
    </div>
    <div class="mb-3">
        <div class="col-12 mb-3 form-group">
        <label for="attachment">{{ __('Attachment') }}:</label>
            
            <div class="image-upload__viewer d-flex">
                <div class="image-viewer__list position-relative">
                    <div class="image-upload__uploader-container">
                        <div class="d-table">
                            <div class="image-upload__uploader">
                                <i class="icon-file-image image-upload__icon"></i>
                                <div class="image-upload__text">{{ __('Upload photos') }}</div>
                                <input type="file" name="attachment" class="image-upload__file-input" accept="image/png,image/jpeg,image/jpg" >
                            </div>
                        </div>
                    </div>
                    <div class="loading">
                        <div class="half-circle-spinner">
                            <div class="circle circle-1"></div>
                            <div class="circle circle-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <button class="btn btn-primary" type="submit">{{ __('Submit') }}</button>
    </div>
</div>
{!! Form::close() !!}
{!! Html::script('vendor/core/plugins/ecommerce/js/utilities.js') !!}