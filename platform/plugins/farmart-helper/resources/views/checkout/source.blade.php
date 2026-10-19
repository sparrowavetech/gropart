<script>
(function () {
    try {
        var key = 'gropart_checkout_source', source = sessionStorage.getItem(key);
        if (!source && !/^\/(admin|payment|checkout)(\/|$)/.test(location.pathname)) {
            var params = new URLSearchParams(location.search), data = {
                landing_domain: location.hostname, landing_page: location.pathname,
                referrer_domain: document.referrer ? new URL(document.referrer).hostname : null
            };
            ['utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid'].forEach(function (name) {
                if (params.has(name)) data[name] = params.get(name).slice(0,190);
            });
            if (params.has('fbclid')) data.fclid = params.get('fbclid').slice(0,190);
            source = JSON.stringify(data); sessionStorage.setItem(key, source);
        }
        function attach() {
            if (!source) return;
            document.querySelectorAll('form').forEach(function (form) {
                if (!form.querySelector('[name="shipping_method"], [name^="shipping_method["], [name="payment_method"], [name="address[email]"]')) return;
                var input = form.querySelector('[name="fh_source"]');
                if (!input) { input = document.createElement('input'); input.type='hidden'; input.name='fh_source'; form.appendChild(input); }
                input.value=source;
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', attach); else attach();
        document.addEventListener('submit', attach, true);
    } catch (_) {}
})();
</script>
