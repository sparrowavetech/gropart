'use strict'

$(() => {
    $(document).on('click', '.add-faq-schema-items', (event) => {
        event.preventDefault()

        $('.faq-schema-items').toggleClass('d-none')
        $(event.currentTarget).toggleClass('d-none');
    })
})
