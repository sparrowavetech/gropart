@extends('plugins/ecommerce::orders.master')

@section('title', theme_option('ecommerce_checkout_seo_title') ?: __('Checkout'))

@section('content')
    @if (Cart::instance('cart')->isNotEmpty())
        @if (is_plugin_active('payment'))
            @include('plugins/payment::partials.header')
        @endif

        {!! $checkoutForm->renderForm() !!}

        @if (is_plugin_active('payment'))
            @include('plugins/payment::partials.footer')
        @endif
    @else
        <div class="container">
            <div class="alert alert-warning my-5">
                <span>{!! BaseHelper::clean(__('No products in cart. :link!', ['link' => Html::link(BaseHelper::getHomepageUrl(), __('Back to shopping'))])) !!}</span>
            </div>
        </div>
    @endif
@stop

@push('footer')
    <style>
        .checkout-products-marketplace .shipping-method-wrapper {
            border: 1px dashed var(--primary-color, #007bff);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1rem;
            max-height: 300px;
            overflow-y: auto;
            padding: 2rem 0.75rem 0.75rem;
        }

        .list_payment_method {
            margin-bottom: 0 !important;
        }
    </style>
    <script type="text/javascript" src="{{ asset('vendor/core/core/base/libraries/jquery-compat/jquery4-compat.js') }}?v={{ EcommerceHelper::getAssetVersion() }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/core/core/js-validation/js/js-validation.js') }}?v={{ EcommerceHelper::getAssetVersion() }}"></script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            function validateShipping() {
                try {
                    const checkoutBtn = document.querySelector('.payment-checkout-btn, button.checkout-button');
                    const vendorShippingBlocks = document.querySelectorAll('.shipping-method-wrapper .list_payment_method');
                    let optionsRequired = 0;
                    let optionsSelected = 0;

                    vendorShippingBlocks.forEach((block) => {
                        const radios = block.querySelectorAll('input[type="radio"]');

                        if (!radios.length) {
                            return;
                        }

                        optionsRequired++;

                        if (block.querySelectorAll('input[type="radio"]:checked').length > 0) {
                            optionsSelected++;

                            return;
                        }

                        const storeId = radios[0].getAttribute('data-id');
                        if (storeId) {
                            const vendorPriceText = document.querySelector('.vendor-shipping-price[data-store-id="' + storeId + '"]');
                            if (vendorPriceText) {
                                vendorPriceText.innerHTML = '<span class="text-muted">- Select Option -</span>';
                            }
                        }
                    });

                    if (document.body.innerHTML.includes('Sorry we do not deliver to this pincode')) {
                        document.querySelectorAll('label').forEach((label) => {
                            if (label.innerText.includes('Free delivery') || label.innerText.includes('Free Delivery')) {
                                const parent = label.closest('li, div.form-check');
                                if (parent) {
                                    parent.style.display = 'none';
                                }
                            }
                        });
                    }

                    if (optionsRequired > 0 && optionsSelected < optionsRequired) {
                        if (checkoutBtn) {
                            checkoutBtn.disabled = true;
                            checkoutBtn.classList.add('disabled');
                            checkoutBtn.style.opacity = '0.5';
                        }

                        document.querySelectorAll('.shipping-price-text:not(.vendor-shipping-price)').forEach((textBlock) => {
                            textBlock.innerHTML = '<span class="text-muted">- Select Option -</span>';
                        });
                    } else if (checkoutBtn) {
                        checkoutBtn.disabled = false;
                        checkoutBtn.classList.remove('disabled');
                        checkoutBtn.style.opacity = '1';
                    }
                } catch (error) {
                    console.error('Shipping validation custom logic error: ', error);
                }
            }

            let observerTimeout;
            const observer = new MutationObserver((mutations) => {
                if (!mutations.some((mutation) => mutation.addedNodes.length)) {
                    return;
                }

                clearTimeout(observerTimeout);
                observerTimeout = setTimeout(validateShipping, 150);
            });

            observer.observe(document.querySelector('.checkout-form') || document.body, {
                childList: true,
                subtree: true,
            });

            document.addEventListener('click', function (event) {
                const checkoutBtn = event.target.closest('.payment-checkout-btn, button.checkout-button');
                if (!checkoutBtn) {
                    return;
                }

                const vendorShippingBlocks = document.querySelectorAll('.shipping-method-wrapper .list_payment_method');
                let optionsRequired = 0;
                let optionsSelected = 0;

                vendorShippingBlocks.forEach((block) => {
                    if (!block.querySelectorAll('input[type="radio"]').length) {
                        return;
                    }

                    optionsRequired++;

                    if (block.querySelectorAll('input[type="radio"]:checked').length > 0) {
                        optionsSelected++;
                    }
                });

                if (optionsRequired > 0 && optionsSelected < optionsRequired) {
                    event.preventDefault();
                    event.stopImmediatePropagation();

                    document.querySelectorAll('.shipping-error-msg').forEach((element) => element.remove());

                    const container = document.querySelector('.checkout-products-marketplace') || document.querySelector('.checkout-shipping-methods-area');
                    if (container) {
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'text-danger shipping-error-msg mt-3 font-weight-bold';
                        errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please select a delivery method for ALL vendors to proceed.';
                        container.appendChild(errorMsg);
                        container.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                        });
                    }
                }
            }, true);

            setTimeout(validateShipping, 500);
        });
    </script>
@endpush
