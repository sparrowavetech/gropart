import fs from 'fs';

function createCdpClient(wsUrl) {
    const ws = new WebSocket(wsUrl);
    let id = 1;
    const callbacks = new Map();

    ws.onmessage = (event) => {
        const msg = JSON.parse(event.data);
        if (msg.id && callbacks.has(msg.id)) {
            const { resolve, reject } = callbacks.get(msg.id);
            callbacks.delete(msg.id);
            if (msg.error) {
                reject(msg.error);
            } else {
                resolve(msg.result);
            }
        }
    };

    return new Promise((resolveReady, rejectReady) => {
        ws.onopen = () => {
            const send = (method, params = {}) => {
                return new Promise((resolve, reject) => {
                    const reqId = id++;
                    callbacks.set(reqId, { resolve, reject });
                    ws.send(JSON.stringify({ id: reqId, method, params }));
                });
            };
            resolveReady({ send, close: () => ws.close() });
        };
        ws.onerror = rejectReady;
    });
}

async function main() {
    console.log("=== Verifying Shimmer Badges and PDP Positioning ===");
    const versionRes = await fetch('http://127.0.0.1:9222/json/version');
    const version = await versionRes.json();
    const browserClient = await createCdpClient(version.webSocketDebuggerUrl);

    // Create a new tab
    const { targetId } = await browserClient.send('Target.createTarget', { url: 'about:blank' });
    const targetWsUrl = `ws://127.0.0.1:9222/devtools/page/${targetId}`;
    const pageClient = await createCdpClient(targetWsUrl);

    await pageClient.send('Page.enable');
    await pageClient.send('DOM.enable');
    await pageClient.send('Runtime.enable');

    await pageClient.send('Emulation.setDeviceMetricsOverride', {
        width: 1280,
        height: 1100,
        deviceScaleFactor: 2,
        mobile: false
    });

    // -------------------------------------------------------------
    // TEST 1: Catalog Card Badges & Shimmer Animation on /products
    // -------------------------------------------------------------
    console.log("\n--- TEST 1: Catalog Cards on /products ---");
    await pageClient.send('Page.navigate', { url: 'http://127.0.0.1:8000/products' });
    await new Promise(r => setTimeout(r, 3500));

    const catalogBadgeCheck = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var badges = document.querySelectorAll('.razorpay-easy-emi-catalog-badge');
            var results = [];
            badges.forEach(function(b, idx) {
                var parent = b.parentElement;
                var pill = b.querySelector('.rzp-badge-pill');
                var cs = window.getComputedStyle(b);
                var pillCs = pill ? window.getComputedStyle(pill) : null;
                results.push({
                    index: idx,
                    scheme: b.getAttribute('data-scheme'),
                    text: b.innerText.trim(),
                    parentTag: parent ? parent.tagName : '',
                    parentClass: parent ? parent.className : '',
                    position: cs.position,
                    top: cs.top,
                    left: cs.left,
                    zIndex: cs.zIndex,
                    pillBackground: pillCs ? pillCs.backgroundImage || pillCs.backgroundColor : '',
                    pillAnimation: pillCs ? pillCs.animationName : '',
                    pillDuration: pillCs ? pillCs.animationDuration : '',
                    pillColor: pillCs ? pillCs.color : '',
                    pillRadius: pillCs ? pillCs.borderRadius : '',
                    hasOffset: b.classList.contains('has-ribbon-offset')
                });
            });
            return {
                totalFound: badges.length,
                details: results.slice(0, 4)
            };
        })()`,
        returnByValue: true
    });

    console.log("Catalog Badges Check:", JSON.stringify(catalogBadgeCheck.result.value, null, 2));

    // Scroll to the first product card with badge and capture screenshot
    await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var b = document.querySelector('.razorpay-easy-emi-catalog-badge');
            if (b) {
                var card = b.closest('.product-item, .product-inner') || b;
                card.scrollIntoView({ behavior: 'instant', block: 'center' });
            }
        })()`
    });
    await new Promise(r => setTimeout(r, 600));

    const catalogScreenshot = await pageClient.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/catalog_badge_shimmer.png', Buffer.from(catalogScreenshot.data, 'base64'));
    console.log("Saved catalog_badge_shimmer.png");

    // -------------------------------------------------------------
    // TEST 2: Product Detail Page (PDP) Placement Verification
    // -------------------------------------------------------------
    console.log("\n--- TEST 2: PDP Placement on Product 111 ---");
    const pdpUrl = 'http://127.0.0.1:8000/products/65-10-6520-for-mitthu-singh-back-tokri-basket-model-thresher-single-kadi-chain';
    await pageClient.send('Page.navigate', { url: pdpUrl });
    await new Promise(r => setTimeout(r, 3500));

    const pdpPlacementCheck = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var wrapper = document.querySelector('.razorpay-easy-emi-container');
            var soldBy = document.querySelector('.product-meta-sold-by');
            var freeShipping = document.querySelector('.product-free-shipping-detail-badge');
            var codBadge = document.querySelector('.product-cod-view-badge');
            var dotList = document.querySelector('.ps-list--dot');
            var shortDescParas = dotList ? Array.from(dotList.querySelectorAll('p')).map(p => p.textContent.trim().substring(0, 40) + '...') : [];

            var order = [];
            var elements = [
                { name: 'soldBy', el: soldBy },
                { name: 'freeShippingBadge', el: freeShipping },
                { name: 'codBadge', el: codBadge },
                { name: 'razorpayEmiWidget', el: wrapper },
                { name: 'firstShortDescP', el: dotList ? dotList.querySelector('p') : null }
            ].filter(item => item.el !== null);

            // Sort by vertical page position (top coordinate)
            elements.sort((a, b) => a.el.getBoundingClientRect().top - b.el.getBoundingClientRect().top);

            return {
                widgetFound: !!wrapper,
                widgetDocked: wrapper ? wrapper.getAttribute('data-docked') : null,
                parentTag: wrapper ? wrapper.parentElement.tagName : null,
                parentClass: wrapper ? wrapper.parentElement.className : null,
                prevElement: wrapper && wrapper.previousElementSibling ? (wrapper.previousElementSibling.className || wrapper.previousElementSibling.tagName) : null,
                nextElement: wrapper && wrapper.nextElementSibling ? (wrapper.nextElementSibling.className || wrapper.nextElementSibling.tagName) : null,
                visualOrder: elements.map(e => ({
                    name: e.name,
                    top: Math.round(e.el.getBoundingClientRect().top),
                    height: Math.round(e.el.getBoundingClientRect().height)
                })),
                shortDescParagraphCount: shortDescParas.length,
                firstParaPreview: shortDescParas[0] || null
            };
        })()`,
        returnByValue: true
    });

    console.log("PDP Placement Check:", JSON.stringify(pdpPlacementCheck.result.value, null, 2));

    // Scroll to the widget and capture screenshot
    await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var w = document.querySelector('.razorpay-easy-emi-container');
            if (w) {
                w.scrollIntoView({ behavior: 'instant', block: 'center' });
            }
        })()`
    });
    await new Promise(r => setTimeout(r, 600));

    const pdpScreenshot = await pageClient.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/pdp_widget_repositioned.png', Buffer.from(pdpScreenshot.data, 'base64'));
    console.log("Saved pdp_widget_repositioned.png");

    // Close tab and finish
    await browserClient.send('Target.closeTarget', { targetId });
    browserClient.close();
    console.log("\n=== Verification Complete ===");
}

main().catch(err => {
    console.error("Verification failed:", err);
    process.exit(1);
});

