<script>
(() => {
    const apply = () => {
        const url = @json(route('enquiry-products.vendor.index'));
        document.querySelectorAll('.ps-sidebar__content a').forEach(link => {
            if (link.href !== url) return;
            link.querySelector('[data-ep-pending]')?.remove();
            const count = @json((int) $count);
            if (!count) return;
            const badge = document.createElement('span');
            badge.dataset.epPending = 'true';
            badge.className = 'badge bg-danger text-white ms-2';
            badge.textContent = count;
            badge.title = @json(__('plugins/enquiry-products::messages.pending_enquiries'));
            badge.setAttribute('aria-label', count + ' ' + @json(__('plugins/enquiry-products::messages.pending_enquiries_e3781')));
            link.append(badge);
        });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', apply);
    else apply();
})();
</script>
