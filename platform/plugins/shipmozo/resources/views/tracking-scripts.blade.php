<script>
    document.addEventListener('DOMContentLoaded', function() {
        const orderIdElement = document.querySelector('.bb-order-info-item .value.fw-bold');
        if (!orderIdElement) return;

        const orderCode = orderIdElement.innerText.replace('#', '').trim();
        if (!orderCode) return;

        fetch('{{ route("shipmozo.public.tracking") }}?order_code=' + orderCode)
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

                const shippingSection = document.querySelector('.bb-order-shipping');
                if (shippingSection) {
                    shippingSection.closest('.card').insertAdjacentHTML('afterend', trackingHtml);
                } else {
                    document.querySelector('.bb-order-detail-wrapper').insertAdjacentHTML('beforeend', trackingHtml);
                }
            })
            .catch(console.error);
    });
</script>
