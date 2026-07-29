'use strict'

class FrontendProductOption {
    constructor() {
        // Product options render on the product detail page and inside the
        // quick-shop modal. Scope every lookup to whichever context the options
        // live in so we never read/write a price from a related-product card.
        const $modal = $('.product-option').first().closest('[data-bb-toggle="quick-shop-modal"], .modal')
        this.$scope = $modal.length ? $modal : $(document)

        // The price near the product title carries data-bb-value="product-price".
        this.priceElement = this.$scope.find('[data-bb-value="product-price"]').first()
        this.priceFormat = this.priceElement.length ? this.parsePriceFormat(this.priceElement.text()) : null
        this.basePrice = this.priceFormat ? this.priceFormat.value : 0

        this.eventListeners()
        this.restoreOptionsFromUrl()

        // Reflect any option already selected at load (e.g. a required radio's
        // first value, or values restored from the URL).
        this.changeDisplayedPrice()
    }

    isInModal($element) {
        return $element.closest('[data-bb-toggle="quick-shop-modal"], .modal').length > 0
    }

    // Detect currency formatting (symbol position, grouping, decimals) from the
    // server-rendered base price. This keeps the live total in the exact same
    // format and makes it currency-switch safe: when the storefront currency is
    // changed the server re-renders the base price, and the option extras are
    // emitted already converted (format_price(..., withoutCurrency: true)), so
    // base and extras are always in the same currency - no hardcoded symbol.
    parsePriceFormat(text) {
        const raw = String(text).trim()
        const firstDigit = raw.search(/\d/)

        if (firstDigit === -1) {
            return null
        }

        let lastDigit = firstDigit
        for (let i = raw.length - 1; i >= 0; i--) {
            if (/\d/.test(raw[i])) {
                lastDigit = i
                break
            }
        }

        const prefix = raw.slice(0, firstDigit)
        const suffix = raw.slice(lastDigit + 1)
        const numberPart = raw.slice(firstDigit, lastDigit + 1)

        // The decimal separator is the last "." or "," followed by 1-2 digits.
        let decimals = 0
        let decimalSep = ''
        const decimalMatch = numberPart.match(/[.,](\d{1,2})$/)
        if (decimalMatch) {
            decimals = decimalMatch[1].length
            decimalSep = decimalMatch[0][0]
        }

        // Anything else between digits is a grouping separator (",", ".", space, nbsp).
        const integerStr = decimalSep ? numberPart.slice(0, -(decimals + 1)) : numberPart
        const groupingMatch = integerStr.match(/\d([.,\s ])\d/)
        const groupingSep = groupingMatch ? groupingMatch[1] : ''

        // Strip grouping separators from the integer part and rejoin with a dot
        // decimal so parseFloat is correct regardless of locale (e.g. "2 900",
        // "2,900.50" and "2.900,50" all parse to their real numeric value).
        const decimalStr = decimalSep ? numberPart.slice(-decimals) : ''
        const value = parseFloat(
            integerStr.replace(/[.,\s ]/g, '') + (decimalSep ? '.' + decimalStr : '')
        )

        return { prefix, suffix, decimals, decimalSep: decimalSep || '.', groupingSep, value }
    }

    formatPriceValue(value) {
        const fmt = this.priceFormat
        const fixed = value.toFixed(fmt.decimals)
        const parts = fixed.split('.')
        const groupedInt = fmt.groupingSep
            ? parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, fmt.groupingSep)
            : parts[0]

        let out = groupedInt
        if (fmt.decimals > 0) {
            out += fmt.decimalSep + parts[1]
        }

        return fmt.prefix + out + fmt.suffix
    }

    eventListeners() {
        // The displayed total is always recomputed from the current DOM state in
        // changeDisplayedPrice(), so each handler just triggers a recalculation
        // and syncs the shareable URL.
        $('.product-option input[type="radio"], .product-option input[type="checkbox"]').change((e) => {
            const $input = $(e.target)
            this.changeDisplayedPrice()
            if (!this.isInModal($input)) {
                this.updateUrlWithOptions()
            }
        })

        $('.product-option select').change((e) => {
            const $select = $(e.target)
            this.changeDisplayedPrice()
            if (!this.isInModal($select)) {
                this.updateUrlWithOptions()
            }
        })

        let fieldTimeout
        $('.product-option input[type="text"]').on('input', (e) => {
            const $input = $(e.target)
            clearTimeout(fieldTimeout)
            fieldTimeout = setTimeout(() => {
                if (!this.isInModal($input)) {
                    this.updateUrlWithOptions()
                }
            }, 500)
        })

        $(window).on('popstate', () => {
            this.restoreOptionsFromUrl()
        })
    }

    changeDisplayedPrice() {
        if (!this.priceFormat || !this.priceElement.length) {
            return
        }

        let extra = 0

        this.$scope.find('.product-option').each((index, element) => {
            const $option = $(element)

            $option.find('input[type="radio"]:checked, input[type="checkbox"]:checked').each((i, input) => {
                extra += parseFloat($(input).attr('data-extra-price')) || 0
            })

            $option.find('select').each((i, select) => {
                extra += parseFloat($(select).find('option:selected').attr('data-extra-price')) || 0
            })
        })

        this.priceElement.text(this.formatPriceValue(this.basePrice + extra))
    }

    updateUrlWithOptions() {
        const url = new URL(window.location)
        const optionSlugs = {}

        $('.product-option').each((index, element) => {
            const $option = $(element)
            const optionSlug = $option.data('option-slug')

            if (!optionSlug) return

            const $checkedRadio = $option.find('input[type="radio"]:checked')
            if ($checkedRadio.length) {
                const valueSlug = $checkedRadio.data('value-slug')
                if (valueSlug) {
                    optionSlugs[optionSlug] = valueSlug
                }
                return
            }

            const $checkedCheckboxes = $option.find('input[type="checkbox"]:checked')
            if ($checkedCheckboxes.length) {
                const values = []
                $checkedCheckboxes.each((i, cb) => {
                    const valueSlug = $(cb).data('value-slug')
                    if (valueSlug) {
                        values.push(valueSlug)
                    }
                })
                if (values.length) {
                    optionSlugs[optionSlug] = values
                }
                return
            }

            const $select = $option.find('select')
            if ($select.length) {
                const $selectedOption = $select.find('option:selected')
                const valueSlug = $selectedOption.data('value-slug')
                if (valueSlug) {
                    optionSlugs[optionSlug] = valueSlug
                }
                return
            }

            const $textField = $option.find('input[type="text"]')
            if ($textField.length && $textField.val()) {
                optionSlugs[optionSlug] = $textField.val()
            }
        })

        const existingOptionSlugs = []
        $('.product-option').each((index, element) => {
            const slug = $(element).data('option-slug')
            if (slug) existingOptionSlugs.push(slug)
        })
        existingOptionSlugs.forEach((slug) => {
            url.searchParams.delete(slug)
        })

        Object.keys(optionSlugs).forEach((key) => {
            const value = optionSlugs[key]
            if (Array.isArray(value)) {
                url.searchParams.set(key, value.join(','))
            } else {
                url.searchParams.set(key, value)
            }
        })

        if (url.href !== window.location.href) {
            window.history.pushState({ options: optionSlugs }, '', url)
        }

        this.updateClipboardButton(url.href)
    }

    updateClipboardButton(url) {
        const $clipboardBtn = $('[data-bb-toggle="social-sharing-clipboard"]')
        if ($clipboardBtn.length) {
            $clipboardBtn.attr('data-clipboard-text', url)
        }
    }

    restoreOptionsFromUrl() {
        const url = new URL(window.location)
        const params = url.searchParams

        $('.product-option').each((index, element) => {
            const $option = $(element)
            const optionSlug = $option.data('option-slug')

            if (!optionSlug || !params.has(optionSlug)) return

            const paramValue = params.get(optionSlug)

            const $radios = $option.find('input[type="radio"]')
            if ($radios.length) {
                $radios.each((i, radio) => {
                    const $radio = $(radio)
                    if ($radio.data('value-slug') === paramValue) {
                        $radio.prop('checked', true).trigger('change')
                    }
                })
                return
            }

            const $checkboxes = $option.find('input[type="checkbox"]')
            if ($checkboxes.length) {
                const values = paramValue.split(',')
                $checkboxes.each((i, cb) => {
                    const $cb = $(cb)
                    const isChecked = values.includes($cb.data('value-slug'))
                    $cb.prop('checked', isChecked)
                })
                $checkboxes.first().trigger('change')
                return
            }

            const $select = $option.find('select')
            if ($select.length) {
                $select.find('option').each((i, opt) => {
                    const $opt = $(opt)
                    if ($opt.data('value-slug') === paramValue) {
                        $select.val($opt.val()).trigger('change')
                    }
                })
                return
            }

            const $textField = $option.find('input[type="text"]')
            if ($textField.length) {
                $textField.val(paramValue)
            }
        })
    }
}

$(() => {
    if ($('.product-option').length) {
        new FrontendProductOption()
    }
})
