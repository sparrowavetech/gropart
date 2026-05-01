<div class="wholesale-success-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 text-center">
                <div class="card">
                    <div class="card-body py-5">
                        <div class="mb-4">
                            <x-core::icon name="ti ti-circle-check" class="text-success" style="font-size: 80px;" />
                        </div>
                        <h2 class="mb-3">{{ trans('plugins/ecommerce-wholesale::wholesale.register.success_title') }}</h2>
                        <p class="text-muted mb-4">
                            {{ trans('plugins/ecommerce-wholesale::wholesale.messages.application_submitted') }}
                        </p>
                        <a href="{{ route('public.index') }}" class="btn btn-primary">
                            {{ trans('plugins/ecommerce-wholesale::wholesale.frontend.back_to_home') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
