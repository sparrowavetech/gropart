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
    document.addEventListener("DOMContentLoaded", function() {
        function validateShipping() {
            try {
                const checkoutBtn = document.querySelector('.payment-checkout-btn, button.checkout-button');

                // Find all active shipping option blocks (one per vendor or store)
                const vendorShippingBlocks = document.querySelectorAll('.shipping-method-wrapper .list_payment_method');
                let optionsRequired = 0;
                let optionsSelected = 0;

                vendorShippingBlocks.forEach(block => {
                    // Check if this block actually has selectable radio options inside it
                    const radios = block.querySelectorAll('input[type="radio"]');
                    const hasRadios = radios.length > 0;
                    if (hasRadios) {
                        optionsRequired++;
                        // Check if one of them is actively checked
                        const isChecked = block.querySelectorAll('input[type="radio"]:checked').length > 0;
                        if (isChecked) {
                            optionsSelected++;
                        } else {
                            // If this specific vendor block isn't selected, wipe its text!
                            const storeId = radios[0].getAttribute('data-id');
                            if (storeId) {
                                const specificVendorPriceText = document.querySelector('.vendor-shipping-price[data-store-id="' + storeId + '"]');
                                if (specificVendorPriceText) {
                                    specificVendorPriceText.innerHTML = '<span class="text-muted">— Select Option —</span>';
                                }
                            }
                        }
                    }
                });

                // Check if the explicitly injected ShipMozo error exists
                const hasShipMozoError = document.body.innerHTML.includes('Sorry we do not deliver to this pincode');

                // Hunt down the 'Free Delivery' label and input to hide it if ShipMozo says no service
                if (hasShipMozoError) {
                    const allLabels = document.querySelectorAll('label');
                    allLabels.forEach(label => {
                        if (label.innerText.includes('Free delivery') || label.innerText.includes('Free Delivery')) {
                            const parentLi = label.closest('li, div.form-check');
                            if (parentLi) {
                                parentLi.style.display = 'none';
                            }
                        }
                    });
                }

                // Strongly disable checkout button if required options are not completely matched
                if (optionsRequired > 0 && optionsSelected < optionsRequired) {
                    if (checkoutBtn) {
                        checkoutBtn.disabled = true;
                        checkoutBtn.classList.add('disabled');
                        checkoutBtn.style.opacity = '0.5';
                    }

                    // Blindly enforce "Select Option" on the main right-side subtotal if it's locked
                    const globalShippingPriceText = document.querySelectorAll('.shipping-price-text:not(.vendor-shipping-price)');
                    globalShippingPriceText.forEach(textBlock => {
                        textBlock.innerHTML = '<span class="text-muted">— Select Option —</span>';
                    });
                } else {
                    if (checkoutBtn) {
                        checkoutBtn.disabled = false;
                        checkoutBtn.classList.remove('disabled');
                        checkoutBtn.style.opacity = '1';
                    }
                }

            } catch (e) {
                console.error("Shipping validation custom logic error: ", e);
            }
        }

        // Re-run validation aggressively when DOM is updated from AJAX
        let observerTimeout;
        const observer = new MutationObserver((mutations) => {
            let shouldRun = false;
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) {
                    shouldRun = true;
                }
            });
            if (shouldRun) {
                clearTimeout(observerTimeout);
                observerTimeout = setTimeout(() => {
                    validateShipping();
                }, 150);
            }
        });

        const shippingWrapper = document.querySelector('.checkout-form') || document.body;
        observer.observe(shippingWrapper, {
            childList: true,
            subtree: true
        });

        // Intercept checkout form submission to strictly force shipping selection
        document.addEventListener('click', function(e) {
            const checkoutBtn = e.target.closest('.payment-checkout-btn, button.checkout-button');
            if (checkoutBtn) {

                const vendorShippingBlocks = document.querySelectorAll('.shipping-method-wrapper .list_payment_method');
                let optionsRequired = 0;
                let optionsSelected = 0;

                vendorShippingBlocks.forEach(block => {
                    const hasRadios = block.querySelectorAll('input[type="radio"]').length > 0;
                    if (hasRadios) {
                        optionsRequired++;
                        const isChecked = block.querySelectorAll('input[type="radio"]:checked').length > 0;
                        if (isChecked) {
                            optionsSelected++;
                        }
                    }
                });

                if (optionsRequired > 0 && optionsSelected < optionsRequired) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    // Clear old errors
                    document.querySelectorAll('.shipping-error-msg').forEach(el => el.remove());

                    // Add error message
                    const container = document.querySelector('.checkout-products-marketplace') || document.querySelector('.checkout-shipping-methods-area');
                    if (container) {
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'text-danger shipping-error-msg mt-3 font-weight-bold';
                        errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please select a delivery method for ALL vendors to proceed.';
                        container.appendChild(errorMsg);

                        // Scroll to wrapper so user sees it
                        container.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                    return false;
                }
            }
        }, true);

        // Initial generic check
        setTimeout(validateShipping, 500);
    });
</script>
@endpush