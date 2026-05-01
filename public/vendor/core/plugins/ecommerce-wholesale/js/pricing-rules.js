'use strict'

$(() => {
    const { lang, rules, groups, discountTypes, productId } = window.wholesalePricingRules || {}

    if (!productId) return

    const PricingRulesManager = {
        rules: rules || [],

        init() {
            this.renderRules()
            this.bindEvents()
        },

        bindEvents() {
            const $wrapper = $('.wholesale-pricing-rules-wrapper')

            $wrapper
                .on('click', '.btn-add-pricing-rule', (e) => {
                    e.preventDefault()
                    this.showRuleModal()
                })
                .on('click', '.btn-edit-rule', (e) => {
                    e.preventDefault()
                    const index = $(e.currentTarget).closest('tr').data('index')
                    this.showRuleModal(index)
                })
                .on('click', '.btn-delete-rule', (e) => {
                    e.preventDefault()
                    const index = $(e.currentTarget).closest('tr').data('index')
                    this.deleteRule(index)
                })
                .on('click', '.btn-save-rule', (e) => {
                    e.preventDefault()
                    this.saveRule()
                })
                .on('change', '#rule_discount_type', () => {
                    this.updateDiscountValueLabel()
                })
        },

        renderRules() {
            const $tbody = $('#pricing-rules-table tbody')
            $tbody.empty()

            if (this.rules.length === 0) {
                $tbody.html(`<tr class="no-rules"><td colspan="5" class="text-center text-muted">${lang.no_rules}</td></tr>`)
                return
            }

            this.rules.forEach((rule, index) => {
                const groupName = rule.customer_group_id ? (groups[rule.customer_group_id] || lang.unknown_group) : lang.all_groups
                const discountDisplay = this.formatDiscount(rule)
                const quantityRange = rule.max_quantity ? `${rule.min_quantity} - ${rule.max_quantity}` : `${rule.min_quantity}+`

                $tbody.append(`
                    <tr data-index="${index}">
                        <td>${quantityRange}</td>
                        <td>${discountDisplay}</td>
                        <td>${groupName}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-primary btn-edit-rule" title="${lang.edit}">
                                <i class="ti ti-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-delete-rule" title="${lang.delete}">
                                <i class="ti ti-trash"></i>
                            </button>
                        </td>
                        <input type="hidden" name="wholesale_pricing_rules[${index}][id]" value="${rule.id || ''}">
                        <input type="hidden" name="wholesale_pricing_rules[${index}][min_quantity]" value="${rule.min_quantity}">
                        <input type="hidden" name="wholesale_pricing_rules[${index}][max_quantity]" value="${rule.max_quantity || ''}">
                        <input type="hidden" name="wholesale_pricing_rules[${index}][discount_type]" value="${rule.discount_type}">
                        <input type="hidden" name="wholesale_pricing_rules[${index}][discount_value]" value="${rule.discount_value}">
                        <input type="hidden" name="wholesale_pricing_rules[${index}][customer_group_id]" value="${rule.customer_group_id || ''}">
                    </tr>
                `)
            })
        },

        formatWithSymbol(value) {
            return lang.is_prefix_symbol ? lang.currency_symbol + value : value + lang.currency_symbol
        },

        formatDiscount(rule) {
            switch (rule.discount_type) {
                case 'percentage':
                    return `${rule.discount_value}%`
                case 'fixed_price':
                    return this.formatWithSymbol(rule.discount_value)
                case 'fixed':
                default:
                    return '-' + this.formatWithSymbol(rule.discount_value)
            }
        },

        showRuleModal(editIndex = null) {
            const isEdit = editIndex !== null
            const rule = isEdit ? this.rules[editIndex] : {
                min_quantity: 1,
                max_quantity: '',
                discount_type: 'percentage',
                discount_value: '',
                customer_group_id: ''
            }

            const groupOptions = Object.entries(groups)
                .map(([id, name]) => `<option value="${id}" ${rule.customer_group_id == id ? 'selected' : ''}>${name}</option>`)
                .join('')

            const discountTypeOptions = Object.entries(discountTypes)
                .map(([value, label]) => `<option value="${value}" ${rule.discount_type === value ? 'selected' : ''}>${label}</option>`)
                .join('')

            const modalHtml = `
                <div class="modal fade" id="pricing-rule-modal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${isEdit ? lang.edit_rule : lang.add_rule}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="rule_edit_index" value="${isEdit ? editIndex : ''}">
                                <input type="hidden" id="rule_id" value="${rule.id || ''}">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">${lang.min_quantity} <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="rule_min_quantity" value="${rule.min_quantity}" min="1" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">${lang.max_quantity}</label>
                                        <input type="number" class="form-control" id="rule_max_quantity" value="${rule.max_quantity || ''}" min="1">
                                        <small class="form-hint">${lang.max_quantity_help}</small>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">${lang.discount_type} <span class="text-danger">*</span></label>
                                        <select class="form-select" id="rule_discount_type">
                                            ${discountTypeOptions}
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" id="discount_value_label">${lang.discount_value} <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="rule_discount_value" value="${rule.discount_value}" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">${lang.customer_group}</label>
                                    <select class="form-select" id="rule_customer_group_id">
                                        <option value="">${lang.all_groups}</option>
                                        ${groupOptions}
                                    </select>
                                    <small class="form-hint">${lang.group_help}</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${lang.cancel}</button>
                                <button type="button" class="btn btn-primary btn-save-rule">${lang.save}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `

            $('#pricing-rule-modal').remove()
            $('body').append(modalHtml)

            const modal = new bootstrap.Modal(document.getElementById('pricing-rule-modal'))
            modal.show()

            this.updateDiscountValueLabel()
        },

        updateDiscountValueLabel() {
            const type = $('#rule_discount_type').val()
            const $label = $('#discount_value_label')
            const $input = $('#rule_discount_value')

            switch (type) {
                case 'percentage':
                    $label.html(`${lang.percentage} (%) <span class="text-danger">*</span>`)
                    $input.attr('max', 100)
                    break
                case 'fixed_price':
                    $label.html(`${lang.fixed_price} <span class="text-danger">*</span>`)
                    $input.removeAttr('max')
                    break
                case 'fixed':
                    $label.html(`${lang.fixed_amount} <span class="text-danger">*</span>`)
                    $input.removeAttr('max')
                    break
            }
        },

        saveRule() {
            const editIndex = $('#rule_edit_index').val()
            const isEdit = editIndex !== ''

            const rule = {
                id: $('#rule_id').val(),
                min_quantity: parseInt($('#rule_min_quantity').val()) || 1,
                max_quantity: $('#rule_max_quantity').val() ? parseInt($('#rule_max_quantity').val()) : null,
                discount_type: $('#rule_discount_type').val(),
                discount_value: parseFloat($('#rule_discount_value').val()) || 0,
                customer_group_id: $('#rule_customer_group_id').val() || null
            }

            if (rule.min_quantity < 1) {
                Botble.showError(lang.min_quantity_required)
                return
            }

            if (rule.discount_value <= 0) {
                Botble.showError(lang.discount_value_required)
                return
            }

            if (rule.discount_type === 'percentage' && rule.discount_value > 100) {
                Botble.showError(lang.percentage_max_100)
                return
            }

            if (rule.max_quantity && rule.max_quantity < rule.min_quantity) {
                Botble.showError(lang.max_must_be_greater)
                return
            }

            if (isEdit) {
                this.rules[parseInt(editIndex)] = rule
            } else {
                this.rules.push(rule)
            }

            this.renderRules()
            bootstrap.Modal.getInstance(document.getElementById('pricing-rule-modal')).hide()
        },

        deleteRule(index) {
            if (confirm(lang.confirm_delete)) {
                this.rules.splice(index, 1)
                this.renderRules()
            }
        }
    }

    PricingRulesManager.init()
})
