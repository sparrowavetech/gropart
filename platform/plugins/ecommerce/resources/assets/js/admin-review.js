const toggleReviewStatus = (url, button) => {
    Botble.showButtonLoading(button)

    $httpClient.make()
        .post(url)
        .then(({ data }) => {
            if (data.error) {
                Botble.showError(data.message)
                return
            }
            Botble.showSuccess(data.message)
            $('#review-section-wrapper').load(window.location.href + ' #review-section-wrapper > *')

            button.closest('.modal').modal('hide')
        })
        .finally(() => {
            Botble.hideButtonLoading(button)
        })
}

$(document)
    .on('click', '[data-bb-toggle="review-delete"]', (event) => {
        $('#confirm-delete-review-button').data('target', $(event.currentTarget).data('target'))
        $('#delete-review-modal').modal('show')
    })
    .on('click', '#confirm-delete-review-button', (event) => {
        const _self = $(event.currentTarget)
        const url = _self.data('target')

        Botble.hideButtonLoading(_self)

        $httpClient.make()
            .delete(url)
            .then(({ data }) => {
                if (data.error) {
                    Botble.showError(data.message)
                    return
                }
                Botble.showSuccess(data.message)
                _self.closest('.modal').modal('hide')

                setTimeout(() => (window.location.href = data?.data?.next_url), 2000)
            })
            .finally(() => {
                Botble.hideButtonLoading(_self)
            })
    })
    .on('click', '[data-bb-toggle="review-unpublish"]', (event) => {
        const button = $(event.currentTarget)

        toggleReviewStatus(route('reviews.unpublish', button.data('id')), button)
    })
    .on('click', '[data-bb-toggle="review-publish"]', (event) => {
        const button = $(event.currentTarget)

        toggleReviewStatus(route('reviews.publish', button.data('id')), button)
    })
