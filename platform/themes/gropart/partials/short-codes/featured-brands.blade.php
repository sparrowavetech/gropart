<div class="ps-product-list mt-40 mb-40">
    <div class="ps-container">
        <div class="ps-section__header">
            <h3>{!! clean($title) !!}</h3>
        </div>
        <featured-brands-component url="{{ route('public.ajax.featured-brands') }}"></featured-brands-component>
    </div>
</div>
