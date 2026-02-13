@php
    $attributes = $attributes ?? [];
    if (is_object($attributes) && method_exists($attributes, 'toArray')) {
        $attributes = $attributes->toArray();
    }

    $productId = (int) ($attributes['product_id'] ?? ($attributes['id'] ?? 0));
@endphp

<div class="form-group mb-3">
    <label class="control-label">{{ trans('plugins/product-bundles::bundles.form.fields.attach_products') }}</label>
    <select name="product_id" class="form-select pb-product-select" data-include-variations="0" data-placeholder="{{ trans('plugins/product-bundles::bundles.form.placeholders.search_product') }}">
        <option value=""></option>
        @if ($productId)
            <option value="{{ $productId }}" selected>#{{ $productId }}</option>
        @endif
    </select>
    <div class="form-text">{{ trans('plugins/product-bundles::bundles.shortcode.description') }}</div>
</div>

<script>
(function () {
    if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) {
        return;
    }

    const $el = jQuery(document.currentScript).closest('div').find('.pb-product-select');
    if (!$el.length || $el.data('select2')) {
        return;
    }

    const ajaxUrl = "{{ route('product-bundles.ajax.products') }}";
    const includeVariations = 0;

    $el.select2({
        width: '100%',
        allowClear: true,
        placeholder: $el.data('placeholder') || '',
        ajax: {
            url: ajaxUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term || '', page: params.page || 1, include_variations: includeVariations };
            },
            processResults: function (data) {
                return {
                    results: data && data.results ? data.results : [],
                    pagination: data && data.pagination ? data.pagination : { more: false }
                };
            },
            cache: true
        }
    });
})();
</script>
