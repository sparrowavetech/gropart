<div class="form-group mb-3">
    <label class="form-label" for="name">{{ __('Full Name') }}:</label>
    <input
        class="form-control"
        id="name"
        name="name"
        type="text"
        value="{{ old('name', $address->name) }}"
    >
    {!! Form::error('name', $errors) !!}
</div>

<div class="form-group mb-3">
    <label class="form-label" for="email">{{ __('Email') }}:</label>
    <input
        class="form-control"
        id="email"
        name="email"
        type="text"
        value="{{ old('email', $address->email) }}"
    >
    {!! Form::error('email', $errors) !!}
</div>

<div class="form-group mb-3">
    <label class="form-label" for="phone">{{ __('Phone') }}:</label>
    <input
        class="form-control"
        id="phone"
        name="phone"
        type="text"
        value="{{ old('phone', $address->phone) }}"
    >
    {!! Form::error('phone', $errors) !!}
</div>

@if (EcommerceHelper::isUsingInMultipleCountries())
    <div class="form-group mb-3 @if ($errors->has('country')) has-error @endif">
        <label class="form-label" for="country">{{ __('Country') }}:</label>
        <select
            class="form-select default-select"
            id="country"
            name="country"
            data-type="country"
        >
            @foreach (EcommerceHelper::getAvailableCountries() as $countryCode => $countryName)
                <option
                    value="{{ $countryCode }}"
                    @if ($address->country == $countryCode) selected @endif
                >{{ $countryName }}</option>
            @endforeach
        </select>
    </div>
    {!! Form::error('country', $errors) !!}
@else
    <input
        name="country"
        type="hidden"
        value="{{ EcommerceHelper::getFirstCountryId() }}"
    >
@endif

<div class="form-group mb-3 @if ($errors->has('state')) has-error @endif">
    <label class="form-label required" for="state">{{ __('State') }}:</label>
    @if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
        <select
            class="form-control"
            id="state"
            name="state"
            data-type="state"
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
            class="form-control"
            id="state"
            name="state"
            type="text"
            value="{{ $address->state }}"
        >
    @endif
    {!! Form::error('state', $errors) !!}
</div>

<div class="form-group mb-3 @if ($errors->has('city')) has-error @endif">
    <label class="form-label required" for="city">{{ __('City') }}:</label>
    @if (EcommerceHelper::useCityFieldAsTextField())
        <input
            class="form-control"
            id="city"
            name="city"
            type="text"
            value="{{ $address->city }}"
        >
    @else
        <select
            class="form-control"
            id="city"
            name="city"
            data-type="city"
            data-using-select2="false"
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
    @endif
    {!! Form::error('city', $errors) !!}
</div>

<div class="form-group mb-3">
    <label class="form-label required" for="address">{{ __('Address') }}:</label>
    <input
        class="form-control"
        id="address"
        name="address"
        type="text"
        value="{{ $address->address }}"
    >
    {!! Form::error('address', $errors) !!}
</div>

@if (EcommerceHelper::isZipCodeEnabled())
    <div class="form-group mb-3 form-group">
        <label class="form-label" for="zip_code">{{ __('Zip code') }}:</label>
        <input
            class="form-control"
            id="zip_code"
            name="zip_code"
            type="text"
            value="{{ $address->zip_code }}"
        >
        {!! Form::error('zip_code', $errors) !!}
    </div>
@endif

<div class="form-group mb-3">
    <label class="control-label" for="is_default">
        <input
            class="customer-checkbox"
            id="is_default"
            name="is_default"
            type="checkbox"
            value="1"
            @if ($address->is_default) checked @endif
        >
        {{ __('Use this address as default.') }}
        {!! Form::error('is_default', $errors) !!}
    </label>
</div>

<div class="form-group mb-3">
    <button
        class="btn btn-primary"
        type="submit"
    >{{ __('Update') }}</button>
</div>
