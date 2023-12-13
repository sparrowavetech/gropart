{!! Form::open(['url' => $url, 'method' => 'POST']) !!}
<div class="form-header py-4">
    <h3>{{ SeoHelper::getTitle() }}</h3>
</div>
<div class="form-content">
    <div class="mb-3">
        <label class="form-label" for="name">{{ __('Full Name') }}:</label>
        <input
            class="form-control @if ($errors->has('name')) is-invalid @endif"
            id="name"
            name="name"
            type="text"
            value="{{ $address->name }}"
            placeholder="{{ __('Enter Full Name') }}"
            required
            minlength="3"
            maxlength="120"
        >
        @if ($errors->has('name'))
            <div class="invalid-feedback">
                {{ $errors->first('name') }}
            </div>
        @endif
    </div>

    <div class="mb-3">
        <label class="form-label" for="email">{{ __('Email') }}:</label>
        <input
            class="form-control @if ($errors->has('email')) is-invalid @endif"
            id="email"
            name="email"
            type="email"
            value="{{ $address->email }}"
            placeholder="{{ __('Enter Email') }}"
            required
        >
        @if ($errors->has('email'))
            <div class="invalid-feedback">
                {{ $errors->first('email') }}
            </div>
        @endif
    </div>

    <div class="mb-3">
        <label class="form-label" for="phone">{{ __('Phone:') }}</label>
        <input
            class="form-control @if ($errors->has('email')) is-invalid @endif"
            id="phone"
            name="phone"
            type="text"
            value="{{ $address->phone }}"
            placeholder="{{ __('Enter Phone') }}"
            required
        >
        @if ($errors->has('phone'))
            <div class="invalid-feedback">
                {{ $errors->first('phone') }}
            </div>
        @endif
    </div>

    <div class="mb-3">
        @if (EcommerceHelper::isUsingInMultipleCountries())
            <label class="form-label" for="country">{{ __('Country') }}:</label>
            <select
                class="form-select @if ($errors->has('state')) is-invalid @endif"
                id="country"
                name="country"
                data-type="country"
                required
            >
                @foreach (EcommerceHelper::getAvailableCountries() as $countryCode => $countryName)
                    <option
                        value="{{ $countryCode }}"
                        @if ($address->country == $countryCode) selected @endif
                    >{{ $countryName }}</option>
                @endforeach
            </select>
        @else
            <input
                name="country"
                type="hidden"
                value="{{ EcommerceHelper::getFirstCountryId() }}"
            >
        @endif
        @if ($errors->has('country'))
            <div class="invalid-feedback">
                {{ $errors->first('country') }}
            </div>
        @endif
    </div>

    <div class="mb-3">
        <label class="form-label" for="state">{{ __('State') }}:</label>
        @if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
            <select
                class="form-select @if ($errors->has('state')) is-invalid @endif"
                id="state"
                name="state"
                data-type="state"
                data-placeholder="{{ __('Select state...') }}"
                data-url="{{ route('ajax.states-by-country') }}"
            >
                <option value="">{{ __('Select state...') }}</option>
                @if (old('country', $address->country) || !EcommerceHelper::isUsingInMultipleCountries())
                    @foreach (EcommerceHelper::getAvailableStatesByCountry(old('country', $address->country)) as $stateId => $stateName)
                        <option
                            value="{{ $stateId }}"
                            @if (old('state', $address->state) == $stateId) selected @endif
                        >{{ $stateName }}</option>
                    @endforeach
                @endif
            </select>
        @else
            <input
                class="form-control @if ($errors->has('state')) is-invalid @endif"
                id="state"
                name="state"
                type="text"
                value="{{ $address->state }}"
                placeholder="{{ __('Enter State') }}"
                required
            >
        @endif
        @if ($errors->has('state'))
            <div class="invalid-feedback">
                {{ $errors->first('state') }}
            </div>
        @endif
    </div>

    <div class="mb-3">
        <label class="form-label" for="city">{{ __('City') }}:</label>
        @if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
            <select
                class="form-select @if ($errors->has('city')) is-invalid @endif"
                id="city"
                name="city"
                data-type="city"
                data-placeholder="{{ __('Select city...') }}"
                data-url="{{ route('ajax.cities-by-state') }}"
            >
                <option value="">{{ __('Select city...') }}</option>
                @if (old('state', $address->state))
                    @foreach (EcommerceHelper::getAvailableCitiesByState(old('state', $address->state)) as $cityId => $cityName)
                        <option
                            value="{{ $cityId }}"
                            @if (old('city', $address->city) == $cityId) selected @endif
                        >{{ $cityName }}</option>
                    @endforeach
                @endif
            </select>
        @else
            <input
                class="form-control"
                id="city"
                name="city"
                type="text"
                value="{{ $address->city }}"
                placeholder="{{ __('Enter City') }}"
            >
        @endif
        @if ($errors->has('city'))
            <div class="invalid-feedback">
                {{ $errors->first('city') }}
            </div>
        @endif
    </div>

    <div class="mb-3">
        <label class="form-label" for="address">{{ __('Address') }}:</label>
        <input
            class="form-control @if ($errors->has('address')) is-invalid @endif"
            id="address"
            name="address"
            type="text"
            value="{{ $address->address }}"
            placeholder="{{ __('Enter Address') }}"
        >
        @if ($errors->has('address'))
            <div class="invalid-feedback">
                {{ $errors->first('address') }}
            </div>
        @endif
    </div>

    @if (EcommerceHelper::isZipCodeEnabled())
        <div class="mb-3">
            <label>{{ __('Zip code') }}:</label>
            <input
                class="form-control @if ($errors->has('zip_code')) is-invalid @endif"
                id="zip_code"
                name="zip_code"
                type="text"
                value="{{ $address->zip_code }}"
                placeholder="{{ __('Enter Zip code') }}"
            >
            @if ($errors->has('zip_code'))
                <div class="invalid-feedback">
                    {{ $errors->first('zip_code') }}
                </div>
            @endif
        </div>
    @endif

    <div class="mb-3">
        <div class="form-check">
            <input
                class="form-check-input"
                id="is-default"
                name="is_default"
                type="checkbox"
                value="1"
                @if ($address->is_default) checked @endif
            >
            <label
                class="form-check-label"
                for="is-default"
            >{{ __('Use this address as default') }}</label>
        </div>
        @if ($errors->has('is_default'))
            <div class="invalid-feedback">
                {{ $errors->first('is_default') }}
            </div>
        @endif
    </div>

    <div class="mb-3">
        <button
            class="btn btn-primary"
            type="submit"
        >{{ __('Save address') }}</button>
    </div>
</div>
{!! Form::close() !!}
