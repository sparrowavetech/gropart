@php
    $rulesData = $rules->map(function ($rule) {
        return [
            'id' => $rule->id,
            'min_quantity' => $rule->min_quantity,
            'max_quantity' => $rule->max_quantity,
            'discount_type' => $rule->discount_type->getValue(),
            'discount_value' => $rule->discount_value,
            'customer_group_id' => $rule->customer_group_id,
        ];
    });

    $discountTypesData = \Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum::labels();

    $currency = get_application_currency();
    $currencySymbol = $currency?->symbol ?? '$';

    $langData = [
        'add_rule' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.add_rule'),
        'edit_rule' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.edit'),
        'no_rules' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.no_rules'),
        'all_groups' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.all_groups'),
        'unknown_group' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.unknown_group'),
        'min_quantity' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.min_quantity'),
        'max_quantity' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.max_quantity'),
        'max_quantity_help' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.max_quantity_help'),
        'discount_type' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount_type'),
        'discount_value' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount_value'),
        'customer_group' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.customer_group'),
        'group_help' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.group_help'),
        'percentage' => trans('plugins/ecommerce-wholesale::wholesale.discount_types.percentage'),
        'fixed_amount' => trans('plugins/ecommerce-wholesale::wholesale.discount_types.fixed'),
        'fixed_price' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.fixed_price'),
        'fixed_price_prefix' => $currencySymbol,
        'currency_symbol' => $currencySymbol,
        'is_prefix_symbol' => (bool) ($currency?->is_prefix_symbol ?? true),
        'save' => trans('core/base::forms.save'),
        'cancel' => trans('core/base::forms.cancel'),
        'edit' => trans('core/base::forms.edit'),
        'delete' => trans('core/base::tables.delete'),
        'confirm_delete' => trans('core/base::tables.confirm_delete'),
        'min_quantity_required' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.min_quantity_required'),
        'discount_value_required' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount_value_required'),
        'percentage_max_100' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.percentage_max_100'),
        'max_must_be_greater' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.max_must_be_greater'),
    ];
@endphp

<div class="wholesale-settings-meta-box">
    {{-- MOQ Settings --}}
    <div class="mb-3">
        <h6 class="text-muted mb-2">{{ trans('plugins/ecommerce-wholesale::wholesale.moq.title') }}</h6>
        <div class="row">
            <div class="col-md-6">
                <label class="form-label" for="wholesale_min_quantity">{{ trans('plugins/ecommerce-wholesale::wholesale.moq.min_quantity') }}</label>
                <input type="number" class="form-control" name="wholesale_min_quantity" id="wholesale_min_quantity" value="{{ $moq?->min_quantity ?? 1 }}" min="1">
                <small class="form-hint">{{ trans('plugins/ecommerce-wholesale::wholesale.moq.min_quantity_help') }}</small>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="wholesale_quantity_increment">{{ trans('plugins/ecommerce-wholesale::wholesale.moq.increment') }}</label>
                <input type="number" class="form-control" name="wholesale_quantity_increment" id="wholesale_quantity_increment" value="{{ $moq?->quantity_increment ?? 1 }}" min="1">
                <small class="form-hint">{{ trans('plugins/ecommerce-wholesale::wholesale.moq.increment_help') }}</small>
            </div>
        </div>
    </div>

    @if(empty($isVendor))
    <hr class="my-3">

    {{-- Visibility Settings --}}
    <div class="mb-3">
        <h6 class="text-muted mb-2">{{ trans('plugins/ecommerce-wholesale::wholesale.visibility.title') }}</h6>
        <div class="mb-3">
            <label class="form-label" for="wholesale_visibility">{{ trans('plugins/ecommerce-wholesale::wholesale.visibility.label') }}</label>
            <select class="form-select" name="wholesale_visibility" id="wholesale_visibility">
                @foreach($visibilityOptions as $value => $label)
                    <option value="{{ $value }}" @selected($selectedVisibility === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <small class="form-hint">{{ trans('plugins/ecommerce-wholesale::wholesale.visibility.help') }}</small>
        </div>
        <div class="mb-3" id="wholesale_group_access_wrapper" style="display: none;">
            <label class="form-label" for="wholesale_group_access">{{ trans('plugins/ecommerce-wholesale::wholesale.visibility.allowed_groups') }}</label>
            @if(!empty($groups))
                <select class="form-select" name="wholesale_group_access[]" id="wholesale_group_access" multiple>
                    @foreach($groups as $groupId => $groupName)
                        <option value="{{ $groupId }}" @selected(in_array($groupId, $groupAccess))>{{ $groupName }}</option>
                    @endforeach
                </select>
            @else
                <div class="alert alert-warning mb-0">
                    <x-core::icon name="ti ti-info-circle" class="me-1" />
                    {{ trans('plugins/ecommerce-wholesale::wholesale.visibility.no_groups') }}
                </div>
            @endif
            <small class="form-hint">{{ trans('plugins/ecommerce-wholesale::wholesale.visibility.allowed_groups_help') }}</small>
        </div>
    </div>
    @endif

    <hr class="my-3">

    {{-- Tiered Pricing Rules --}}
    <div class="wholesale-pricing-rules-wrapper" id="wholesale-pricing-rules-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="text-muted mb-0">{{ trans('plugins/ecommerce-wholesale::wholesale.tiered_pricing') }}</h6>
            <button type="button" class="btn btn-sm btn-info btn-add-pricing-rule" id="btn-add-pricing-rule">
                <x-core::icon name="ti ti-plus" /> {{ trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.add_rule') }}
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0" id="pricing-rules-table">
                <thead>
                    <tr>
                        <th>{{ trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.quantity_range') }}</th>
                        <th>{{ trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount') }}</th>
                        <th>{{ trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.group') }}</th>
                        <th class="text-end" style="width: 100px;">{{ trans('core/base::tables.operations') }}</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Rules will be rendered by JavaScript --}}
                </tbody>
            </table>
        </div>
        <input type="hidden" name="has_wholesale_pricing_rules" value="1">
    </div>
</div>

<script>
(function() {
    'use strict';

    const config = {
        productId: {{ $product->id }},
        rules: {!! json_encode($rulesData) !!},
        groups: {!! json_encode($groups) !!},
        discountTypes: {!! json_encode($discountTypesData) !!},
        lang: {!! json_encode($langData) !!}
    };

    const iconEdit = `<x-core::icon name="ti ti-edit" />`;
    const iconTrash = `<x-core::icon name="ti ti-trash" />`;

    const { lang, groups, discountTypes, productId } = config;
    let rules = config.rules || [];

    function renderRules() {
        const $tbody = $('#pricing-rules-table tbody');
        $tbody.empty();

        if (rules.length === 0) {
            $tbody.html('<tr class="no-rules"><td colspan="4" class="text-center text-muted">' + lang.no_rules + '</td></tr>');
            return;
        }

        rules.forEach(function(rule, index) {
            const groupName = rule.customer_group_id ? (groups[rule.customer_group_id] || lang.unknown_group) : lang.all_groups;
            const discountDisplay = formatDiscount(rule);
            const quantityRange = rule.max_quantity ? rule.min_quantity + ' - ' + rule.max_quantity : rule.min_quantity + '+';

            $tbody.append(
                '<tr data-index="' + index + '">' +
                    '<td>' + quantityRange + '</td>' +
                    '<td>' + discountDisplay + '</td>' +
                    '<td>' + groupName + '</td>' +
                    '<td class="text-end">' +
                        '<button type="button" class="btn btn-sm btn-icon btn-outline-primary btn-edit-rule" title="' + lang.edit + '">' +
                            iconEdit +
                        '</button> ' +
                        '<button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-delete-rule" title="' + lang.delete + '">' +
                            iconTrash +
                        '</button>' +
                    '</td>' +
                    '<input type="hidden" name="wholesale_pricing_rules[' + index + '][id]" value="' + (rule.id || '') + '">' +
                    '<input type="hidden" name="wholesale_pricing_rules[' + index + '][min_quantity]" value="' + rule.min_quantity + '">' +
                    '<input type="hidden" name="wholesale_pricing_rules[' + index + '][max_quantity]" value="' + (rule.max_quantity || '') + '">' +
                    '<input type="hidden" name="wholesale_pricing_rules[' + index + '][discount_type]" value="' + rule.discount_type + '">' +
                    '<input type="hidden" name="wholesale_pricing_rules[' + index + '][discount_value]" value="' + rule.discount_value + '">' +
                    '<input type="hidden" name="wholesale_pricing_rules[' + index + '][customer_group_id]" value="' + (rule.customer_group_id || '') + '">' +
                '</tr>'
            );
        });
    }

    function formatDiscount(rule) {
        var sym = lang.currency_symbol;
        var prefix = lang.is_prefix_symbol;

        function formatWithSymbol(value) {
            return prefix ? sym + value : value + sym;
        }

        switch (rule.discount_type) {
            case 'percentage':
                return rule.discount_value + '%';
            case 'fixed_price':
                return formatWithSymbol(rule.discount_value);
            case 'fixed':
            default:
                return '-' + formatWithSymbol(rule.discount_value);
        }
    }

    function showRuleModal(editIndex) {
        const isEdit = editIndex !== undefined && editIndex !== null;
        const rule = isEdit ? rules[editIndex] : {
            min_quantity: 1,
            max_quantity: '',
            discount_type: 'percentage',
            discount_value: '',
            customer_group_id: ''
        };

        let groupOptions = '';
        for (const [id, name] of Object.entries(groups)) {
            groupOptions += '<option value="' + id + '"' + (rule.customer_group_id == id ? ' selected' : '') + '>' + name + '</option>';
        }

        let discountTypeOptions = '';
        for (const [value, label] of Object.entries(discountTypes)) {
            discountTypeOptions += '<option value="' + value + '"' + (rule.discount_type === value ? ' selected' : '') + '>' + label + '</option>';
        }

        const modalHtml =
            '<div class="modal fade" id="pricing-rule-modal" tabindex="-1">' +
                '<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header">' +
                            '<h5 class="modal-title">' + (isEdit ? lang.edit_rule : lang.add_rule) + '</h5>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                        '</div>' +
                        '<div class="modal-body">' +
                            '<input type="hidden" id="rule_edit_index" value="' + (isEdit ? editIndex : '') + '">' +
                            '<input type="hidden" id="rule_id" value="' + (rule.id || '') + '">' +
                            '<div class="row mb-3">' +
                                '<div class="col-md-6">' +
                                    '<label class="form-label">' + lang.min_quantity + ' <span class="text-danger">*</span></label>' +
                                    '<input type="number" class="form-control" id="rule_min_quantity" value="' + rule.min_quantity + '" min="1" required>' +
                                '</div>' +
                                '<div class="col-md-6">' +
                                    '<label class="form-label">' + lang.max_quantity + '</label>' +
                                    '<input type="number" class="form-control" id="rule_max_quantity" value="' + (rule.max_quantity || '') + '" min="1">' +
                                    '<small class="form-hint">' + lang.max_quantity_help + '</small>' +
                                '</div>' +
                            '</div>' +
                            '<div class="row mb-3">' +
                                '<div class="col-md-6">' +
                                    '<label class="form-label">' + lang.discount_type + ' <span class="text-danger">*</span></label>' +
                                    '<select class="form-select" id="rule_discount_type">' + discountTypeOptions + '</select>' +
                                '</div>' +
                                '<div class="col-md-6">' +
                                    '<label class="form-label" id="discount_value_label">' + lang.discount_value + ' <span class="text-danger">*</span></label>' +
                                    '<input type="number" class="form-control" id="rule_discount_value" value="' + rule.discount_value + '" step="0.01" min="0" required>' +
                                '</div>' +
                            '</div>' +
                            '<div class="mb-3">' +
                                '<label class="form-label">' + lang.customer_group + '</label>' +
                                '<select class="form-select" id="rule_customer_group_id">' +
                                    '<option value="">' + lang.all_groups + '</option>' +
                                    groupOptions +
                                '</select>' +
                                '<small class="form-hint">' + lang.group_help + '</small>' +
                            '</div>' +
                        '</div>' +
                        '<div class="modal-footer">' +
                            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' + lang.cancel + '</button>' +
                            '<button type="button" class="btn btn-primary" id="btn-save-rule">' + lang.save + '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        $('#pricing-rule-modal').remove();
        $('body').append(modalHtml);

        const modalEl = document.getElementById('pricing-rule-modal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        $('#btn-save-rule').on('click', function() {
            saveRule();
        });

        $('#rule_discount_type').on('change', function() {
            updateDiscountValueLabel();
        });

        updateDiscountValueLabel();
    }

    function updateDiscountValueLabel() {
        const type = $('#rule_discount_type').val();
        const $label = $('#discount_value_label');
        const $input = $('#rule_discount_value');

        switch (type) {
            case 'percentage':
                $label.html(lang.percentage + ' (%) <span class="text-danger">*</span>');
                $input.attr('max', 100);
                break;
            case 'fixed_price':
                $label.html(lang.fixed_price + ' <span class="text-danger">*</span>');
                $input.removeAttr('max');
                break;
            case 'fixed':
                $label.html(lang.fixed_amount + ' <span class="text-danger">*</span>');
                $input.removeAttr('max');
                break;
        }
    }

    function saveRule() {
        const editIndex = $('#rule_edit_index').val();
        const isEdit = editIndex !== '';

        const rule = {
            id: $('#rule_id').val(),
            min_quantity: parseInt($('#rule_min_quantity').val()) || 1,
            max_quantity: $('#rule_max_quantity').val() ? parseInt($('#rule_max_quantity').val()) : null,
            discount_type: $('#rule_discount_type').val(),
            discount_value: parseFloat($('#rule_discount_value').val()) || 0,
            customer_group_id: $('#rule_customer_group_id').val() || null
        };

        if (rule.min_quantity < 1) {
            Botble.showError(lang.min_quantity_required);
            return;
        }

        if (rule.discount_value <= 0) {
            Botble.showError(lang.discount_value_required);
            return;
        }

        if (rule.discount_type === 'percentage' && rule.discount_value > 100) {
            Botble.showError(lang.percentage_max_100);
            return;
        }

        if (rule.max_quantity && rule.max_quantity < rule.min_quantity) {
            Botble.showError(lang.max_must_be_greater);
            return;
        }

        if (isEdit) {
            rules[parseInt(editIndex)] = rule;
        } else {
            rules.push(rule);
        }

        renderRules();
        bootstrap.Modal.getInstance(document.getElementById('pricing-rule-modal')).hide();
    }

    function deleteRule(index) {
        if (confirm(lang.confirm_delete)) {
            rules.splice(index, 1);
            renderRules();
        }
    }

    $(function() {
        renderRules();

        $('#btn-add-pricing-rule').on('click', function(e) {
            e.preventDefault();
            showRuleModal();
        });

        $('#pricing-rules-table').on('click', '.btn-edit-rule', function(e) {
            e.preventDefault();
            const index = $(this).closest('tr').data('index');
            showRuleModal(index);
        });

        $('#pricing-rules-table').on('click', '.btn-delete-rule', function(e) {
            e.preventDefault();
            const index = $(this).closest('tr').data('index');
            deleteRule(index);
        });

        const visibilitySelect = document.getElementById('wholesale_visibility');
        const groupAccessWrapper = document.getElementById('wholesale_group_access_wrapper');

        if (visibilitySelect && groupAccessWrapper) {
            function toggleGroupAccess() {
                groupAccessWrapper.style.display = visibilitySelect.value === 'specific_groups' ? 'block' : 'none';
            }

            visibilitySelect.addEventListener('change', toggleGroupAccess);
            toggleGroupAccess();
        }
    });
})();
</script>
