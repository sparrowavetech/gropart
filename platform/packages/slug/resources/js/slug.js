class PermalinkField {
    constructor() {
        let $slugBox = $(document).find('.slug-field-wrapper')

        $(document).on('blur', `#${$slugBox.data('field-name')}`, (e) => {
            if ($slugBox.find('input[name="slug"]').is('[readonly]')) {
                return
            }

            const value = $(e.currentTarget).val()

            if (value !== null && value !== '' && ! $slugBox.find('input[name="slug"]').val()) {
                createSlug(value, 0)
            }
        })

        $(document).on('change', 'input[name="slug"]', (event) => {
            const input = $(event.currentTarget)

            $slugBox = $(document).find('.slug-field-wrapper')

            if ($slugBox.has('.slug-data').length === 0) {
                return
            }

            const value = input.val()

            const id = $slugBox.find('.slug-data').data('id') || 0

            if (value !== null && value !== '') {
                createSlug(value, id)
            } else {
                input.addClass('is-invalid')
            }
        })

        $(document).on('click', '[data-bb-toggle="slug-edit"]', (event) => {
            $slugBox = $(document).find('.slug-field-wrapper')

            $slugBox.find('input[name="slug"]')
                .prop('readonly', false)
                .focus()

            $(event.currentTarget).hide()

            $slugBox.find('[data-bb-toggle="slug-ok"]').show()
        })

        $(document).on('click', '[data-bb-toggle="slug-ok"]', (event) => {
            $slugBox = $(document).find('.slug-field-wrapper')

            $slugBox.find('input[name="slug"]')
                .prop('readonly', true)

            $(event.currentTarget).hide()

            $slugBox.find('[data-bb-toggle="slug-edit"]').show()
        })

        const toggleInputSlugState = () => {
            const slugInput = $slugBox.find('input[name="slug"]')

            if (slugInput.prop('readonly')) {
                slugInput.prop('readonly', false)

                slugInput.closest('.mb-3.position-relative').find('.spinner-border').remove()
            } else {
                slugInput.prop('readonly', true)

                slugInput.closest('.mb-3.position-relative').append(
                    `<div
                    class="spinner-border spinner-border-sm text-secondary"
                    role="status"
                    style="border-radius: 50%; position: absolute; top: 2.5rem; z-index: 3; ${slugInput.hasClass('is-invalid') || slugInput.hasClass('is-valid') ? 'inset-inline-end: 5rem;' : 'inset-inline-end: 1rem;'}"
                ></div>`
                )
            }
        }

        /**
         * @param {string} value
         * @param {number} id
         */
        const createSlug = (value, id) => {
            const form = $slugBox.closest('form')
            const $slugId = $slugBox.find('.slug-data')

            toggleInputSlugState()

            $httpClient
                .make()
                .post($slugId.data('url'), {
                    value: value,
                    slug_id: id.toString(),
                    model: form.find('input[name="model"]').val(),
                })
                .then(({ data }) => {
                    toggleInputSlugState()

                    const url = `${$slugId.data('view')}${data.toString().replace('/', '')}`

                    $slugBox.find('input[name="slug"]').val(data)
                    form.find('.page-url-seo p').text(url)
                    $slugBox.find('.slug-current').val(data)
                })
        }
    }
}

$(() => {
    new PermalinkField()
})
