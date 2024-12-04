<section id="order-details" class="order-tracking">
    <div class="row justify-content-center">
        <div class="col-md-6">
            {!! $form->renderForm() !!}
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            @include(EcommerceHelper::viewPath('includes.order-tracking-detail'))
        </div>
    </div>
</section>
