<script>
    document.addEventListener('DOMContentLoaded', function() {
        const trackingParams = @json(array_filter($trackingParams ?? []));
        if (!Object.keys(trackingParams).length) return;

        fetch('{{ route("shipmozo.public.tracking") }}?' + new URLSearchParams(trackingParams), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(response => {
                if (response.error || !response.data) return;

                const trackingHtml = `
                    <div class="card mb-4 mt-4 border-info">
                        <div class="card-body">
                            <h5 class="bb-section-title mb-3">Live Tracking (ShipMozo)</h5>
                            <div class="shipmozo-live-tracking">
                                ${response.data}
                            </div>
                        </div>
                    </div>
                `;

                const shippingSection = document.querySelector('.bb-order-shipping, [data-order-shipping]');
                if (shippingSection) {
                    (shippingSection.closest('.card') || shippingSection).insertAdjacentHTML('afterend', trackingHtml);
                } else {
                    const wrapper = document.querySelector('.bb-order-detail-wrapper, .order-tracking, main');
                    wrapper?.insertAdjacentHTML('beforeend', trackingHtml);
                }
            })
            .catch(() => {});
    });
</script>
