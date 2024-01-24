$(function () {
    const $wrapper = $('[data-bb-toggle="admin-email"]')

    if (!$wrapper.length) {
        return
    }

    const $addBtn = $wrapper.find('#add')
    let max = parseInt($wrapper.data('max'), 10)

    let emails = $wrapper.data('emails')

    if (emails.length === 0) {
        emails = ['']
    }

    const onAddEmail = () => {
        let count = $wrapper.find('input[type=email]').length

        if (count >= max) {
            $addBtn.addClass('disabled')
        } else {
            $addBtn.removeClass('disabled')
        }
    }

    const addEmail = (value = '') => {
        return $wrapper.find('label').after(`<div class="d-flex mt-2 more-email align-items-center">
                <input type="email" class="form-control" placeholder="${$addBtn.data(
                    'placeholder'
                )}" name="admin_email[]" value="${value ? value : ''}" />
                <a class="btn btn-link text-danger"><i class="fas fa-minus"></i></a>
            </div>`)
    }

    const render = () => {
        emails.forEach((email) => {
            addEmail(email)
        })

        onAddEmail()
    }

    $wrapper.on('click', '.more-email > a', function () {
        if ($(this).hasClass('disabled')) {
            return
        }

        $(this).parent('.more-email').remove()

        onAddEmail()
    })

    $addBtn.on('click', (e) => {
        e.preventDefault()
        addEmail()
        onAddEmail()
    })

    render()
})
