{!! Html::script('vendor/core/plugins/ecommerce/js/checkout.js?v=1.2.0') !!}

@if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
<script src="{{ asset('vendor/core/plugins/location/js/location.js') }}?v=1.2.0"></script>
@endif
{!! Form::open(['url' =>route('public.enquiry.form'), 'class' => 'contact-form', 'method' => 'POST','enctype'=>'multipart/form-data']) !!}
<input type="hidden" name="product_id" value="{{ $product->id}}">

<div class="row mt-5">
    <div class="col-lg-4 product-details-content">
        <div class="card">
            <img class="card-img-top lazyload product-thumbnail__img" src="{{ image_placeholder($product->image, 'small') }}" data-src="{{ RvMedia::getImageUrl($product->image, 'small', false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}">
            <div class="card-body">
                <h4 class="product_title entry-title">{{ $product->name }}</h4>
                @if ($product->categories->count())
                    <div class="meta-categories">
                        <span class="meta-label d-inline-block fw-bold">{{ __('Categories') }}: </span>
                        @foreach($product->categories as $category)
                            <a href="{{ $category->url }}">{!! BaseHelper::clean($category->name) !!}</a>@if (!$loop->last), @endif
                        @endforeach
                    </div>
                @endif
                @if ($product->brand_id)
                    <p class="mb-0 me-2 pe-2 text-secondary"><strong>{{ __('Brand') }}:</strong> <a href="{{ $product->brand->url }}">{{ $product->brand->name }}</a></p>
                @endif
                @if (is_plugin_active('marketplace') && $product->store_id)
                    <div class="product-meta-sold-by my-2">
                        <span class="d-inline-block fw-bold">{{ __('Sold By') }}: </span>
                        <a href="{{ $product->store->url }}">
                            {{ $product->store->name }}
                        </a>
                        @if($product->store->is_verified)
                            <img class="verified-store-main" src="{{ asset('/storage/stores/verified.png')}}"alt="Verified">
                        @endif
                        <small class="badge bg-warning text-dark">{{ $product->store->shop_category->label() }}</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="form-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="mb-3">
                        <input id="name" type="text" class="form-control py-3 px-3 @if ($errors->has('name')) is-invalid @endif" name="name" value="{{ auth()->user()->name }}" placeholder="{{ __('Enter Full Name') }} *" required minlength="3" maxlength="120">
                        @if ($errors->has('name'))
                        <div class="invalid-feedback">
                            {{ $errors->first('name') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <input id="email" type="email" class="form-control py-3 px-3 @if ($errors->has('email')) is-invalid @endif" name="email" value="{{ auth()->user()->email }}" placeholder="{{ __('Enter Email') }} *" required>
                        @if ($errors->has('email'))
                        <div class="invalid-feedback">
                            {{ $errors->first('email') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <input id="phone" type="text" class="form-control py-3 px-3 @if ($errors->has('phone')) is-invalid @endif" name="phone" value="{{ auth()->user()->phone }}" placeholder="{{ __('Enter Phone') }} *" required>
                        @if ($errors->has('phone'))
                        <div class="invalid-feedback">
                            {{ $errors->first('phone') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="@if (EcommerceHelper::isUsingInMultipleCountries()) col-lg-4 @endif">
                    <div class="@if (EcommerceHelper::isUsingInMultipleCountries()) mb-3 @endif">
                        @if (EcommerceHelper::isUsingInMultipleCountries())
                            <select name="country" class="form-select py-3 px-3 @if ($errors->has('state')) is-invalid @endif" id="country" data-type="country" required>
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
                </div>

                <div class="@if (EcommerceHelper::isUsingInMultipleCountries()) col-lg-4 @else col-lg-6 @endif">
                    <div class="mb-3">
                        @if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
                        <select name="state" class="form-select py-3 px-3 @if ($errors->has('state')) is-invalid @endif" id="state" data-type="state" data-placeholder="{{ __('Select state...') }}" data-url="{{ route('ajax.states-by-country') }}">
                            <option value="">{{ __('Select state...') }}</option>
                            @if (old('country', auth()->user()->country) || !EcommerceHelper::isUsingInMultipleCountries())
                            @foreach(EcommerceHelper::getAvailableStatesByCountry(old('country', auth()->user()->country)) as $stateId => $stateName)
                            <option value="{{ $stateId }}" @if (old('state', auth()->user()->state) == $stateId) selected @endif>{{ $stateName }}</option>
                            @endforeach
                            @endif
                        </select>
                        @else
                        <input id="state" type="text" class="form-control py-3 px-3 @if ($errors->has('state')) is-invalid @endif" name="state" value="{{ $address->state }}" placeholder="{{ __('Enter State') }}" required>
                        @endif
                        @if ($errors->has('state'))
                        <div class="invalid-feedback">
                            {{ $errors->first('state') }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="@if (EcommerceHelper::isUsingInMultipleCountries()) col-lg-4 @else col-lg-6 @endif">
                    <div class="mb-3">
                        @if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation())
                            <select name="city" class="form-select py-3 px-3 @if ($errors->has('city')) is-invalid @endif" id="city" data-type="city" data-placeholder="{{ __('Select city...') }} *" data-url="{{ route('ajax.cities-by-state') }}">
                                <option value="">{{ __('Select city...') }}</option>
                                @if (old('state', auth()->user()->state))
                                @foreach(EcommerceHelper::getAvailableCitiesByState(old('state', auth()->user()->state)) as $cityId => $cityName)
                                <option value="{{ $cityId }}" @if (old('city', auth()->user()->city) == $cityId) selected @endif>{{ $cityName }}</option>
                                @endforeach
                                @endif
                            </select>
                        @else
                            <input id="city" type="text" class="form-control py-3 px-3" name="city" value="{{ auth()->user()->city }}" placeholder="{{ __('Enter City') }} *">
                        @endif
                        @if ($errors->has('city'))
                        <div class="invalid-feedback">
                            {{ $errors->first('city') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="@if (EcommerceHelper::isZipCodeEnabled()) col-lg-6 @else col-lg-12 @endif">
                    <div class="mb-3">
                        <input id="address" type="text" class="form-control py-3 px-3 @if ($errors->has('address')) is-invalid @endif" name="address" value="{{auth()->user()->address }}" placeholder="{{ __('Address') }} (Optional)">
                        @if ($errors->has('address'))
                        <div class="invalid-feedback">
                            {{ $errors->first('address') }}
                        </div>
                        @endif
                    </div>
                </div>
                @if (EcommerceHelper::isZipCodeEnabled())
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <input id="zip_code" type="text" class="form-control py-3 px-3 @if ($errors->has('zip_code')) is-invalid @endif" name="zip_code" value="{{ auth()->user()->zip_code }}" placeholder="{{ __('Enter Zip code') }} (Optional)">
                            @if ($errors->has('zip_code'))
                            <div class="invalid-feedback">
                                {{ $errors->first('zip_code') }}
                            </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="mb-3">
                        <textarea name="description" id="" cols="30" rows="10" class="form-control py-3 px-3" name="description" placeholder="{{ __('Additional Requirement Details') }} (Optional)"></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3 form-group">
                        <div class="mb-2"><label for="attachment">{{ __('Add Requirement Attachment') }} (Optional):</label></div>
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
                <div class="col-lg-6 text-right">
                    <div class="mt-4">
                        <button class="btn btn-primary btn-lg btn-submit" type="submit">{{ __('Send Requirement') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{!! Form::close() !!}
{!! Html::script('vendor/core/plugins/ecommerce/js/utilities.js') !!}
