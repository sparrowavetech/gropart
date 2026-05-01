class LoyaltyProductVariation {
    constructor() {
        this.config = window.loyaltyPointsConfig || null
        if (!this.config) return

        this.init()
    }

    init() {
        const self = this
        const existingCallback = window.onChangeSwatchesSuccess

        window.onChangeSwatchesSuccess = (response, element) => {
            if (typeof existingCallback === 'function') {
                existingCallback(response, element)
            } else {
                self.handleDefaultEcommerceBehavior(response, element)
            }

            self.handleVariationChange(response)
        }
    }

    handleDefaultEcommerceBehavior(response, element) {
        if (!response) {
            return
        }

        const $product = $('.bb-product-detail')
        const $form = element.closest('form')
        const $button = $form.find('button[type="submit"]')
        const $quantity = $form.find('input[name="qty"]')
        const $available = $product.find('.number-items-available')
        const $sku = $product.find('[data-bb-value="product-sku"]')

        const { error, data } = response

        if (error) {
            $button.prop('disabled', true)
            $quantity.prop('disabled', true)
            $form.find('input[name="id"]').val('')
            return
        }

        $button.prop('disabled', false)
        $quantity.prop('disabled', false)
        $form.find('input[name="id"]').val(data.id)

        $product.find('[data-bb-value="product-price"]').text(data.display_sale_price)

        if (data.sale_price !== data.price) {
            $product.find('[data-bb-value="product-original-price"]').text(data.display_price).show()
        } else {
            $product.find('[data-bb-value="product-original-price"]').hide()
        }

        if (data.sku) {
            $sku.text(data.sku)
            $sku.closest('div').show()
        } else {
            $sku.closest('div').hide()
        }

        if (data.error_message) {
            $button.prop('disabled', true)
            $quantity.prop('disabled', true)
            $available.html(`<span class='text-danger'>${data.error_message}</span>`).show()
        } else if (data.warning_message) {
            $available.html(`<span class='text-warning fw-medium fs-6'>${data.warning_message}</span>`).show()
        } else if (data.success_message) {
            $available.html(`<span class='text-success'>${data.success_message}</span>`).show()
        } else {
            $available.html('').hide()
        }

        $product.find('.bb-product-attribute-swatch-item').removeClass('disabled')
        $product.find('.bb-product-attribute-swatch-list select option').prop('disabled', false)

        const unavailableAttributeIds = data.unavailable_attribute_ids || []

        if (unavailableAttributeIds.length) {
            unavailableAttributeIds.forEach((id) => {
                let $swatchItem = $product.find(`.bb-product-attribute-swatch-item[data-id="${id}"]`)

                if ($swatchItem.length) {
                    $swatchItem.addClass('disabled')
                    $swatchItem.find('input').prop('checked', false)
                } else {
                    $product.find(`.bb-product-attribute-swatch-list select option[value="${id}"]`).prop('disabled', true)
                }
            })
        }
    }

    handleVariationChange(res) {
        if (!res || !res.data) return

        const data = res.data
        const newPrice = parseFloat(data.sale_price)
        if (isNaN(newPrice) || newPrice <= 0) return

        const points = this.calculatePoints(newPrice)
        const maxPoints = this.calculateMaxPoints(newPrice)
        const maxDiscount = this.calculateMaxDiscount(newPrice, maxPoints)

        this.updateDOM(newPrice, points, maxPoints, maxDiscount)
    }

    calculatePoints(amount) {
        const { earningRate, earningCurrency, tierMultiplier } = this.config
        if (earningCurrency <= 0) return 0

        const basePoints = Math.floor((amount / earningCurrency) * earningRate)
        return Math.floor(basePoints * tierMultiplier)
    }

    calculateMaxPoints(amount) {
        const { redemptionRate, redemptionCurrency, pointsExchangeRate } = this.config
        if (redemptionCurrency <= 0) return 0

        return Math.floor(((amount * pointsExchangeRate) / redemptionCurrency) * redemptionRate)
    }

    calculateMaxDiscount(amount, maxPoints) {
        const { redemptionRate, redemptionCurrency, pointsExchangeRate, maxRedemptionPercentage } = this.config
        if (redemptionRate <= 0) return 0

        let discount = ((maxPoints / redemptionRate) * redemptionCurrency) / pointsExchangeRate

        if (maxRedemptionPercentage > 0) {
            const maxAllowed = (amount * maxRedemptionPercentage) / 100
            if (discount > maxAllowed) {
                discount = maxAllowed
            }
        }

        return discount
    }

    updateDOM(price, points, maxPoints, maxDiscount) {
        const box = document.querySelector('.loyalty-product-info-box')
        if (!box) return

        box.dataset.productPrice = price
        box.dataset.pointsToEarn = points
        box.dataset.maxPoints = maxPoints
        box.dataset.maxDiscount = maxDiscount

        const pointsElements = box.querySelectorAll('.loyalty-points-earn')
        pointsElements.forEach(el => {
            el.textContent = '+' + this.formatNumber(points)
        })

        const discountElements = box.querySelectorAll('.loyalty-max-discount')
        discountElements.forEach(el => {
            el.textContent = this.formatPrice(maxDiscount)
        })

        const maxPointsTextElements = box.querySelectorAll('.loyalty-max-points-text')
        maxPointsTextElements.forEach(el => {
            const template = el.dataset.template || 'Using :points points'
            el.textContent = template.replace(':points', this.formatNumber(maxPoints))
        })

        this.updateTipText(box, points, maxPoints, maxDiscount)
        this.toggleVisibility(box, points, maxPoints, maxDiscount)
    }

    updateTipText(box, points, maxPoints, maxDiscount) {
        const tipElements = box.querySelectorAll('.loyalty-tip-text')

        tipElements.forEach(tipEl => {
            if (tipEl.dataset.tipBoth || tipEl.dataset.tipEarn || tipEl.dataset.tipRedeem) {
                let text = ''
                if (points > 0 && maxPoints > 0 && tipEl.dataset.tipBoth) {
                    text = tipEl.dataset.tipBoth
                        .replace(':earn_points', this.formatNumber(points))
                        .replace(':max_discount', this.formatPrice(maxDiscount))
                } else if (points > 0 && tipEl.dataset.tipEarn) {
                    text = tipEl.dataset.tipEarn.replace(':points', this.formatNumber(points))
                } else if (maxPoints > 0 && tipEl.dataset.tipRedeem) {
                    text = tipEl.dataset.tipRedeem.replace(':discount', this.formatPrice(maxDiscount))
                }

                if (text) {
                    tipEl.textContent = text
                }
            }
        })
    }

    toggleVisibility(box, points, maxPoints, maxDiscount) {
        if (points <= 0 && maxPoints <= 0) {
            box.style.display = 'none'
        } else {
            box.style.display = ''
        }

        const earnSections = box.querySelectorAll('.loyalty-earn-section')
        earnSections.forEach(el => {
            el.style.display = points > 0 ? '' : 'none'
        })

        const earnLabels = box.querySelectorAll('.loyalty-earn-label')
        earnLabels.forEach(el => {
            el.style.display = points > 0 ? '' : 'none'
        })

        const redeemSections = box.querySelectorAll('.loyalty-redeem-section')
        redeemSections.forEach(el => {
            el.style.display = (maxPoints > 0 && maxDiscount > 0) ? '' : 'none'
        })

        const redeemSeparators = box.querySelectorAll('.loyalty-redeem-separator')
        redeemSeparators.forEach(el => {
            el.style.display = (maxPoints > 0 && maxDiscount > 0) ? '' : 'none'
        })
    }

    formatNumber(num) {
        const { thousandsSeparator } = this.config
        return Math.floor(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator || ',')
    }

    formatPrice(amount) {
        const { currencySymbol, currencyPosition, thousandsSeparator, decimalSeparator, decimals } = this.config

        let formatted = amount.toFixed(decimals || 0)

        if (decimalSeparator && decimalSeparator !== '.') {
            formatted = formatted.replace('.', decimalSeparator)
        }

        const parts = formatted.split(decimalSeparator || '.')
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator || ',')
        formatted = parts.join(decimalSeparator || '.')

        if (currencyPosition === 'after' || currencyPosition === 'after_with_space') {
            const space = currencyPosition === 'after_with_space' ? ' ' : ''
            return formatted + space + currencySymbol
        }

        const space = currencyPosition === 'before_with_space' ? ' ' : ''
        return currencySymbol + space + formatted
    }
}

new LoyaltyProductVariation()
