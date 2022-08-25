export class CheckoutAddress {
    init() {
        $(document).on('change', '#address_id', event => {
            if ($(event.currentTarget).val() !== 'new') {
                $('.address-item-selected').show().html($('.list-available-address .address-item-wrapper[data-id=' + $(event.currentTarget).val() + ']').html());
                $('.address-form-wrapper').hide();
            } else {
                $('.address-item-selected').hide();
                $('.address-form-wrapper').show();
            }
        });

        $(document).on('click', '#create_account', event => {
            if ($(event.currentTarget).is(':checked')) {
                $('.password-group').fadeIn();
            } else {
                $('.password-group').fadeOut();
            }
        });

        $(document).on('click', '#billing_address_same_as_shipping_address', event => {
            if ($(event.currentTarget).is(':checked')) {
                $('.billing-address-form-wrapper').fadeOut();
            } else {
                $('.billing-address-form-wrapper').fadeIn();
            }
        });
    }
}
