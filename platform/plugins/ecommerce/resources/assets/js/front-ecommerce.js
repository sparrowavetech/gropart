class Ecommerce {
    quickSearchAjax = null

    constructor() {
        $(document)
            .on('click', '[data-bb-toggle="toggle-product-categories-tree"]', (e) => {
                e.preventDefault()

                const currentTarget = $(e.currentTarget)

                currentTarget.toggleClass('active')
                currentTarget.closest('.bb-product-filter-item').find('> .bb-product-filter-items').slideToggle()
            })
            .on('click', '[data-bb-toggle="toggle-filter-sidebar"]', () => {
                $('.bb-filter-offcanvas-area').toggleClass('offcanvas-opened')
                $('.body-overlay').toggleClass('opened')
            })
            .on('click', '.body-overlay', () => {
                $('.bb-filter-offcanvas-area').removeClass('offcanvas-opened')
                $('.body-overlay').removeClass('opened')
            })
            .on('submit', 'form.bb-product-form-filter', (e) => {
                e.preventDefault()

                const currentTarget = $(e.currentTarget)

                const formData = this.#transformFormData(currentTarget.serializeArray())
                let nextUrl = `${window.location.origin}${window.location.pathname}`
                let params = []

                formData.map((item) => {
                    params.push(`${encodeURIComponent(item.name)}=${encodeURIComponent(item.value)}`)
                })

                if (params.length) {
                    nextUrl += `?${params.join('&')}`
                }

                formData.push({ name: '_', value: Date.now() })

                if (window.location.href === nextUrl) {
                    return
                }

                $.ajax({
                    url: currentTarget.prop('action'),
                    type: 'GET',
                    data: formData,
                    beforeSend: () => {
                        document.dispatchEvent(
                            new CustomEvent('ecommerce.product-filter.before', {
                                detail: {
                                    data: formData,
                                    element: currentTarget,
                                },
                            })
                        )
                    },
                    success: (data) => {
                        const { message, error } = data

                        if (error) {
                            Theme.showError(message)

                            return
                        }

                        window.history.pushState(formData, null, nextUrl)

                        document.dispatchEvent(
                            new CustomEvent('ecommerce.product-filter.success', {
                                detail: {
                                    data,
                                    element: currentTarget,
                                },
                            })
                        )
                    },
                    error: (error) => Theme.handleError(error),
                    complete: () => {
                        document.dispatchEvent(
                            new CustomEvent('ecommerce.product-filter.completed', {
                                detail: {
                                    element: currentTarget,
                                },
                            })
                        )
                    },
                })
            })
            .on('change', 'form.bb-product-form-filter input, form.bb-product-form-filter select', (e) => {
                $(e.currentTarget).closest('form').trigger('submit')
            })
            .on('keyup', '.bb-form-quick-search input', (e) => {
                this.#ajaxSearchProducts($(e.currentTarget).closest('form'))
            })
            .on('click', 'body', (e) => {
                if (!$(e.target).closest('.bb-form-quick-search').length) {
                    $('.bb-quick-search-results').removeClass('show').html('')
                }
            })
            .on('click', '[data-bb-toggle="quick-search-load-more"]', (e) => {
                e.preventDefault()

                const currentTarget = $(e.currentTarget)

                this.#ajaxSearchProducts(currentTarget.closest('form'), currentTarget.prop('href'))
            })

        this.initPriceFilter()
    }

    /**
     * @returns {boolean}
     */
    isRtl() {
        return document.body.getAttribute('dir') === 'rtl'
    }

    /**
     * @param {JQuery} element
     */
    initLightGallery(element) {
        if (!element.length) {
            return
        }

        if (element.data('lightGallery')) {
            element.data('lightGallery').destroy(true)
        }

        element.lightGallery({
            selector: 'a',
            thumbnail: true,
            share: false,
            fullScreen: false,
            autoplay: false,
            autoplayControls: false,
            actualSize: false,
        })
    }

    initProductGallery() {
        const $gallery = $('.bb-product-gallery-images')
        const $thumbnails = $('.bb-product-thumbnails')

        if ($gallery.length) {
            $gallery.map((index, item) => {
                const $item = $(item)
                if ($item.hasClass('slick-initialized')) {
                    $item.slick('unslick')
                }

                $item.slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: false,
                    dots: false,
                    infinite: false,
                    fade: true,
                    lazyLoad: 'ondemand',
                    asNavFor: '.bb-product-thumbnails',
                    rtl: this.isRtl(),
                })
            })
        }

        if ($thumbnails.length) {
            $thumbnails.map((index, item) => {
                const $item = $(item)
                if ($item.hasClass('slick-initialized')) {
                    $item.slick('unslick')
                }

                $item.slick({
                    slidesToShow: 6,
                    slidesToScroll: 1,
                    arrows: true,
                    asNavFor: '.bb-product-gallery-images',
                    focusOnSelect: true,
                    infinite: false,
                    variableWidth: true,
                    centerMode: true,
                    rtl: this.isRtl(),
                    prevArrow:
                        '<button class="slick-prev slick-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 6l-6 6l6 6" /></svg></button>',
                    nextArrow:
                        '<button class="slick-next slick-arrow"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-right" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6" /></svg></button>',
                    responsive: [
                        {
                            breakpoint: 992,
                            settings: {
                                slidesToShow: 4,
                            },
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 3,
                            },
                        },
                    ],
                })
            })
        }

        this.initLightGallery($gallery)
    }

    initPriceFilter() {
        if (typeof $.fn.slider === 'undefined') {
            throw new Error('jQuery UI slider is required for price filter')
        }

        const $priceFilter = $('.bb-product-price-filter')
        const $sliderRange = $priceFilter.find('.price-slider')
        const $rangeLabel = $priceFilter.find('.input-range-label')

        if ($priceFilter) {
            const $minPrice = $priceFilter.find('input[name="min_price"]')
            const $maxPrice = $priceFilter.find('input[name="max_price"]')

            $sliderRange.slider({
                range: true,
                min: $sliderRange.data('min'),
                max: $sliderRange.data('max'),
                values: [$minPrice.val(), $maxPrice.val()],
                slide: function (event, ui) {
                    $rangeLabel.find('.from').text(ui.values[0])
                    $rangeLabel.find('.to').text(ui.values[1])
                },
                change: function (event, ui) {
                    if (parseInt($minPrice.val()) !== ui.values[0]) {
                        $minPrice.val(ui.values[0]).trigger('change')
                    }

                    if (parseInt($maxPrice.val()) !== ui.values[1]) {
                        $maxPrice.val(ui.values[1]).trigger('change')
                    }
                },
            })

            $rangeLabel.find('.from').text($sliderRange.slider('values', 0))
            $rangeLabel.find('.to').text($sliderRange.slider('values', 1))
        }
    }

    #transformFormData = (formData) => {
        let data = []

        formData.map((item) => {
            if (item.value) {
                data.push(item)
            }
        })

        return data
    }

    #ajaxSearchProducts = (form, url) => {
        const button = form.find('button[type="submit"]')
        const input = form.find('input[name="q"]')
        const results = form.find('.bb-quick-search-results')

        if (!input.val()) {
            results.removeClass('show').html('')

            return
        }

        this.quickSearchAjax = $.ajax({
            type: 'GET',
            url: url || form.data('ajax-url'),
            data: form.serialize(),
            beforeSend: () => {
                button.addClass('btn-loading')

                if (!url) {
                    results.removeClass('show').html('')
                }

                if (this.quickSearchAjax !== null) {
                    this.quickSearchAjax.abort()
                }
            },
            success: ({ error, message, data }) => {
                if (error) {
                    Theme.showError(message)

                    return
                }

                results.addClass('show')

                if (url) {
                    results.find('.bb-quick-search-list').append($(data).find('.bb-quick-search-list').html())
                } else {
                    results.html(data)
                }
            },
            complete: () => button.removeClass('btn-loading'),
        })
    }
}

$(() => {
    window.EcommerceApp = new Ecommerce()
})
