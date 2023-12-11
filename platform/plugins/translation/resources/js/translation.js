$(() => {
    $('#botble-translation-tables-translation-table').on('draw.dt', () => {
        $('.editable')
        .editable({ mode: 'inline' })
        .on('hidden', (event, reason) => {
            let locale = $(event.currentTarget).data('locale')
            if (reason === 'save') {
                $(event.currentTarget).removeClass('status-0').addClass('status-1')
            }
            if (reason === 'save' || reason === 'nochange') {
                let $next = $(event.currentTarget).closest('tr').next().find(`.editable.locale-${locale}`)
                setTimeout(() => {
                    $next.editable('show')
                }, 300)
            }
        })
    })

    $('.group-select').on('change', (event) => {
        const group = $(event.currentTarget).val()

        const url = new URL(window.location.href)

        if (group) {
            url.searchParams.set('group', group)
        } else {
            url.searchParams.delete('group')
        }

        window.history.pushState({}, '', url)
        $('#botble-translation-tables-translation-table').DataTable().ajax.url(url.href).load()
    })

    $('.box-translation').on('click', '.button-import-groups', (event) => {
        event.preventDefault()

        const $button = $(event.currentTarget)
        const $form = $button.closest('form')

        $httpClient
            .make()
            .withButtonLoading($button)
            .postForm($form.prop('action'), new FormData($form[0]))
            .then(({ data }) => {
                Botble.showSuccess(data.message)

                setTimeout(() => {
                    window.location.reload()
                }, 1000)
            })
    })
})
