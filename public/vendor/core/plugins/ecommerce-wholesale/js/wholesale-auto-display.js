/**
 * Wholesale auto-display fallback.
 *
 * Renders the wholesale pricing box on product pages whose theme does NOT call the
 * `ecommerce_after_product_description` hook. Loaded (by the InjectWholesaleBoxLoader
 * middleware) only on product pages where the server did not already render the box.
 *
 * Anchor: the core ecommerce add-to-cart form, present on every product page in every
 * theme (`form[action*="add-to-cart"]` with a hidden `input[name="id"]`). The box is
 * inserted immediately before that form. No external dependencies (no jQuery).
 */
(function () {
    'use strict';

    function alreadyRendered() {
        return !!document.querySelector('[data-wholesale-box]');
    }

    function getEndpointBase() {
        var tag = document.querySelector('script[data-wholesale-endpoint]');

        return tag ? tag.getAttribute('data-wholesale-endpoint') : null;
    }

    function getProductForm() {
        return document.querySelector('form[action*="add-to-cart"]');
    }

    // Inserts box HTML before the form. Scripts parsed from innerHTML do not execute,
    // so re-create them to preserve the table's quantity/price behavior.
    function injectBeforeForm(form, html) {
        var template = document.createElement('template');
        template.innerHTML = html;

        Array.prototype.forEach.call(template.content.querySelectorAll('script'), function (oldScript) {
            var script = document.createElement('script');

            Array.prototype.forEach.call(oldScript.attributes, function (attr) {
                script.setAttribute(attr.name, attr.value);
            });

            script.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(script, oldScript);
        });

        form.parentNode.insertBefore(template.content, form);
    }

    function init() {
        if (alreadyRendered()) {
            return;
        }

        var base = getEndpointBase();
        var form = getProductForm();

        if (!base || !form) {
            return;
        }

        var input = form.querySelector('input[name="id"]');

        if (!input || !input.value) {
            return;
        }

        var url = base.replace(/\/+$/, '') + '/' + encodeURIComponent(input.value);

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.ok ? response.json() : null;
            })
            .then(function (data) {
                if (!data || !data.html || alreadyRendered()) {
                    return;
                }

                injectBeforeForm(form, data.html);
            })
            .catch(function () {});
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
