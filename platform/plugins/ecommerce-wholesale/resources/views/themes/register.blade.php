<div class="wholesale-register-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">{{ trans('plugins/ecommerce-wholesale::wholesale.register.title') }}</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.apply_form_description') }}
                        </p>

                        {!! $form->renderForm() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
