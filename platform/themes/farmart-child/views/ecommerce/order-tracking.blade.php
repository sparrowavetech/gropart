<section id="order-details" class="order-tracking py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            {!! $form->renderForm() !!}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            @include(Theme::getThemeNamespace('views.ecommerce.includes.order-tracking-detail'))
        </div>
    </div>
</section>
@if (setting('phone_number_enable_country_code', true))
    @once
        @include('core/base::forms.fields.phone-number-script')
    @endonce
@endif
