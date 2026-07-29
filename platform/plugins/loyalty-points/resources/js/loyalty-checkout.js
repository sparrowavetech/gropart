class LoyaltyPointsCheckout {
    constructor() {
        this.translations = window.loyaltyPointsTranslations || {
            pointsApplied: 'Loyalty points discount applied',
            pointsRemoved: 'Loyalty points removed',
            invalidPointsAmount: 'Invalid points amount',
            genericError: 'An error occurred. Please try again.'
        }
        this.init()
    }

    init() {
        this.handleQuickApply()
        this.handleRemovePoints()
        this.listenForCartUpdates()
    }

    listenForCartUpdates() {
        if (this.cartUpdateListenerAttached) return

        document.addEventListener('checkout:cart-updated', () => {
            this.reloadLoyaltyBlock()
        })

        this.cartUpdateListenerAttached = true
    }

    handleQuickApply() {
        const buttons = document.querySelectorAll('.loyalty-preset-btn, #quick-apply-loyalty-points-btn')
        if (!buttons.length) return

        buttons.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault()

                const points = parseInt(btn.dataset.points)
                if (!points || points <= 0) {
                    this.showInlineError(this.translations.invalidPointsAmount)
                    return
                }

                buttons.forEach(b => {
                    b.disabled = true
                    b.setAttribute('aria-disabled', 'true')
                })
                btn.classList.add('loading')

                try {
                    const response = await fetch(btn.dataset.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ points }),
                    })

                    const data = await response.json()

                    if (data.error) {
                        this.showInlineError(data.message)
                        buttons.forEach(b => {
                            b.disabled = false
                            b.removeAttribute('aria-disabled')
                        })
                        btn.classList.remove('loading')
                    } else {
                        this.showSuccess(data.message)
                        document.dispatchEvent(new CustomEvent('coupon:applied'))
                        this.refreshOrderSummary()
                        this.reloadLoyaltyBlock()
                    }
                } catch (error) {
                    this.showInlineError(this.translations.genericError)
                    buttons.forEach(b => {
                        b.disabled = false
                        b.removeAttribute('aria-disabled')
                    })
                    btn.classList.remove('loading')
                }
            })
        })
    }

    handleRemovePoints() {
        const removeBtn = document.getElementById('remove-loyalty-points-btn')
        if (!removeBtn) return

        removeBtn.addEventListener('click', async (e) => {
            e.preventDefault()

            removeBtn.disabled = true
            removeBtn.setAttribute('aria-disabled', 'true')
            removeBtn.classList.add('loading')

            try {
                const response = await fetch(removeBtn.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })

                const data = await response.json()

                if (data.error) {
                    this.showInlineError(data.message)
                    removeBtn.disabled = false
                    removeBtn.removeAttribute('aria-disabled')
                    removeBtn.classList.remove('loading')
                } else {
                    this.showSuccess(data.message)
                    document.dispatchEvent(new CustomEvent('coupon:removed'))
                    this.refreshOrderSummary()
                    this.reloadLoyaltyBlock()
                }
            } catch (error) {
                this.showInlineError(this.translations.genericError)
                removeBtn.disabled = false
                removeBtn.removeAttribute('aria-disabled')
                removeBtn.classList.remove('loading')
            }
        })
    }

    async reloadLoyaltyBlock() {
        const currentBlock = document.querySelector('.loyalty-points-compact')
        if (!currentBlock) return

        currentBlock.classList.add('loading')
        currentBlock.setAttribute('aria-busy', 'true')

        try {
            const response = await fetch(window.location.href)
            const html = await response.text()
            const parser = new DOMParser()
            const doc = parser.parseFromString(html, 'text/html')

            const newBlock = doc.querySelector('.loyalty-points-compact')

            if (newBlock && currentBlock) {
                const hadFocusInBlock = currentBlock.contains(document.activeElement)

                currentBlock.replaceWith(newBlock)
                this.init()

                if (hadFocusInBlock) {
                    const focusTarget = newBlock.querySelector('button:not(:disabled)')
                    if (focusTarget) {
                        focusTarget.focus()
                    }
                }

                const isApplied = newBlock.querySelector('.loyalty-applied')
                this.announceStatus(isApplied
                    ? this.translations.pointsApplied
                    : this.translations.pointsRemoved)
            }
        } catch (error) {
            console.error('Failed to reload loyalty block', error)
            currentBlock.classList.remove('loading')
            currentBlock.removeAttribute('aria-busy')
        }
    }

    refreshOrderSummary() {
        // After points are applied/removed the ecommerce order summary (the
        // points discount line and the total) must be recalculated. We dispatch
        // `coupon:applied`/`coupon:removed` for that, but older ecommerce builds
        // do not listen to those events on the checkout summary, so the total
        // would only refresh once the customer changed the shipping method.
        //
        // Re-fire the change event on the currently selected shipping method so
        // ecommerce re-runs its own (proven) shipping calculation, which
        // re-renders the summary with the loyalty discount while keeping the
        // selected shipping method intact.
        const checkedShipping = document.querySelector('input.shipping_method_input:checked')
        if (checkedShipping) {
            checkedShipping.dispatchEvent(new Event('change', { bubbles: true }))
        }
    }

    announceStatus(message) {
        const statusEl = document.getElementById('loyalty-status')
        if (statusEl) {
            statusEl.textContent = message
            setTimeout(() => { statusEl.textContent = '' }, 1000)
        }
    }

    showInlineError(message) {
        const container = document.querySelector('.loyalty-points-compact')
        if (!container) {
            this.showError(message)
            return
        }

        const existingError = container.querySelector('.loyalty-error')
        if (existingError) existingError.remove()

        const errorEl = document.createElement('div')
        errorEl.className = 'loyalty-error mt-2'
        errorEl.setAttribute('role', 'alert')
        errorEl.innerHTML = `
            <span class="d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                ${message}
            </span>
        `
        container.appendChild(errorEl)

        setTimeout(() => errorEl.remove(), 5000)

        this.announceStatus(message)
    }

    showError(message) {
        if (typeof MainCheckout !== 'undefined' && MainCheckout.showError) {
            MainCheckout.showError(message)
        } else if (typeof Theme !== 'undefined' && Theme.showError) {
            Theme.showError(message)
        } else if (typeof Botble !== 'undefined' && Botble.showError) {
            Botble.showError(message)
        } else {
            this.showModal('Error', message, 'danger')
        }
    }

    showSuccess(message) {
        if (typeof MainCheckout !== 'undefined' && MainCheckout.showSuccess) {
            MainCheckout.showSuccess(message)
        } else if (typeof Theme !== 'undefined' && Theme.showSuccess) {
            Theme.showSuccess(message)
        } else if (typeof Botble !== 'undefined' && Botble.showSuccess) {
            Botble.showSuccess(message)
        } else {
            this.showModal('Success', message, 'success')
        }
    }

    showModal(title, message, type) {
        const modalId = 'loyalty-message-modal'
        let modal = document.getElementById(modalId)

        if (!modal) {
            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-${type} text-white">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${message}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `
            document.body.insertAdjacentHTML('beforeend', modalHtml)
            modal = document.getElementById(modalId)
        } else {
            modal.querySelector('.modal-title').textContent = title
            modal.querySelector('.modal-body').textContent = message
            const header = modal.querySelector('.modal-header')
            header.className = `modal-header bg-${type} text-white`
        }

        let bsModal;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bsModal = new bootstrap.Modal(modal)
        } else if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
            bsModal = new window.bootstrap.Modal(modal)
        } else {
            alert(message)
            return
        }

        bsModal.show()
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new LoyaltyPointsCheckout()
    })
} else {
    new LoyaltyPointsCheckout()
}
