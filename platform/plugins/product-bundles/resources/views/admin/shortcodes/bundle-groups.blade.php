@php
    $attributes = $attributes ?? [];
    if (is_object($attributes) && method_exists($attributes, 'toArray')) {
        $attributes = $attributes->toArray();
    }

    $title = (string) ($attributes['title'] ?? '');
    $subtitle = (string) ($attributes['subtitle'] ?? '');
    $limit = (int) ($attributes['limit'] ?? 8);
    $layout = (string) ($attributes['layout'] ?? 'grid');
    $type = (string) ($attributes['type'] ?? 'all');
    $featuredOnly = (int) ($attributes['featured_only'] ?? 0);
@endphp

<div class="form-group mb-3">
    <label class="control-label">{{ trans('plugins/product-bundles::bundles.shortcode_groups.title') }}</label>
    <input name="title" class="form-control" value="{{ $title }}">
</div>

<div class="form-group mb-3">
    <label class="control-label">{{ trans('plugins/product-bundles::bundles.shortcode_groups.subtitle') }}</label>
    <input name="subtitle" class="form-control" value="{{ $subtitle }}">
</div>

<div class="row g-3">
    <div class="col-md-4">
        <label class="control-label">{{ trans('plugins/product-bundles::bundles.shortcode_groups.limit') }}</label>
        <input name="limit" type="number" min="1" class="form-control" value="{{ $limit ?: 8 }}">
    </div>

    <div class="col-md-4">
        <label class="control-label">{{ trans('plugins/product-bundles::bundles.shortcode_groups.layout') }}</label>
        <select name="layout" class="form-select">
            @foreach (trans('plugins/product-bundles::bundles.shortcode_groups.layouts') as $k => $label)
                <option value="{{ $k }}" @selected($layout === $k)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="control-label">{{ trans('plugins/product-bundles::bundles.shortcode_groups.type') }}</label>
        <select name="type" class="form-select">
            @foreach (trans('plugins/product-bundles::bundles.shortcode_groups.types') as $k => $label)
                <option value="{{ $k }}" @selected($type === $k)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group mt-3">
    <label class="form-check">
        <input class="form-check-input" type="checkbox" name="featured_only" value="1" @checked($featuredOnly)>
        <span class="form-check-label">{{ trans('plugins/product-bundles::bundles.shortcode_groups.featured_only') }}</span>
    </label>
</div>
