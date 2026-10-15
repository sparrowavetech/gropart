<div id="ep-store-catalog-link" class="ep-shell" style="margin-top:1rem;margin-bottom:1rem">
    <a class="ep-button" href="{{ $url }}">{{ __('plugins/enquiry-products::messages.view_enquiry_catalogue') }}</a>
</div>
<script>
(() => {
    const insert = () => {
        const link = document.getElementById('ep-store-catalog-link');
        const form = document.querySelector('.products-filter-form-vendor');
        const main = document.querySelector('main, [role="main"]');
        if (form) form.before(link);
        else if (main) main.prepend(link);
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', insert); else insert();
})();
</script>
