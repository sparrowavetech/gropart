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
    console.log("=== Connecting to Chrome CDP ===");
    const versionRes = await fetch('http://127.0.0.1:9222/json/version');
    const version = await versionRes.json();
    console.log("Connected to:", version.Browser);

    const browserClient = await createCdpClient(version.webSocketDebuggerUrl);
    const { targetId } = await browserClient.send('Target.createTarget', { url: 'about:blank' });
    const targetWsUrl = `ws://127.0.0.1:9222/devtools/page/${targetId}`;
    const pageClient = await createCdpClient(targetWsUrl);

    await pageClient.send('Page.enable');
    await pageClient.send('DOM.enable');
    await pageClient.send('Runtime.enable');

    await pageClient.send('Emulation.setDeviceMetricsOverride', {
        width: 1280,
        height: 1000,
        deviceScaleFactor: 2,
        mobile: false
    });

    // 1. Search for Vexnol to see catalog card
    console.log("\n--- Checking Vexnol Catalog Search Page ---");
    await pageClient.send('Page.navigate', { url: 'http://127.0.0.1:8000/products?keyword=Vexnol' });
    await new Promise(r => setTimeout(r, 3500));

    const searchEval = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var badges = document.querySelectorAll('.razorpay-easy-emi-catalog-badge');
            var badgeInfo = [];
            badges.forEach(function(b) {
                var style = window.getComputedStyle(b);
                var pill = b.querySelector('.rzp-badge-pill');
                var pillStyle = pill ? window.getComputedStyle(pill) : null;
                var afterStyle = pill ? window.getComputedStyle(pill, '::after') : null;
                var card = b.closest('.product-inner, .product-item, .product-card, article');
                var thumb = card ? card.querySelector('.product-thumbnail') : null;
                var seller = card ? card.querySelector('.sold-by-meta') : null;
                
                var bRect = b.getBoundingClientRect();
                var tRect = thumb ? thumb.getBoundingClientRect() : null;
                var sRect = seller ? seller.getBoundingClientRect() : null;

                badgeInfo.push({
                    text: pill ? pill.innerText.trim() : '',
                    bottom: style.bottom,
                    left: style.left,
                    top: style.top,
                    zIndex: style.zIndex,
                    pillBorder: pillStyle ? pillStyle.border : null,
                    pillBg: pillStyle ? pillStyle.background : null,
                    afterAnimation: afterStyle ? afterStyle.animationName : null,
                    isAboveSeller: sRect && bRect ? (bRect.bottom <= sRect.top + 5) : false,
                    badgeRect: { top: bRect.top, bottom: bRect.bottom, left: bRect.left },
                    thumbRect: tRect ? { bottom: tRect.bottom } : null,
                    sellerRect: sRect ? { top: sRect.top } : null
                });
            });

            var soldByMetas = document.querySelectorAll('.sold-by-meta');
            var soldInfo = [];
            soldByMetas.forEach(function(s) {
                var store = s.querySelector('a')?.innerText.trim();
                var verified = s.querySelector('.vendor-verified-label')?.innerText.trim();
                var type = s.querySelector('.vendor-shop-type-badge')?.innerText.trim();
                var sStyle = window.getComputedStyle(s);
                soldInfo.push({
                    store: store,
                    verified: verified,
                    type: type,
                    display: sStyle.display,
                    flexDirection: sStyle.flexDirection
                });
            });

            return {
                badgesCount: badges.length,
                badgeInfo: badgeInfo,
                soldCount: soldByMetas.length,
                soldInfo: soldInfo.slice(0, 5)
            };
        })()`,
        returnByValue: true
    });

    console.log("Search Page Evaluation:\n", JSON.stringify(searchEval.result.value, null, 2));

    const searchScreenshot = await pageClient.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/vexnol_search_card_badge.png', Buffer.from(searchScreenshot.data, 'base64'));
    console.log("Saved: vexnol_search_card_badge.png");

    // 2. PDP for Vexnol
    console.log("\n--- Checking Vexnol PDP ---");
    await pageClient.send('Page.navigate', { url: 'http://127.0.0.1:8000/products/vexnol-ep-90-extreme-pressure-gear-and-transmission-oil-10-litre-bucket' });
    await new Promise(r => setTimeout(r, 3500));

    const pdpEval = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var widget = document.querySelector('.razorpay-easy-emi-container');
            if (!widget) return { found: false };

            // Check if there is any standalone pill badge inside or immediately before the widget
            var standalonePill = widget.querySelector('.badge.bg-warning-subtle, .badge.bg-success-subtle');
            var customBadge = widget.querySelector('.badge:not(.rzp-badge-pill)');
            var header = widget.querySelector('.rzp-card-header');

            return {
                found: true,
                hasStandalonePill: !!standalonePill,
                hasCustomBadge: !!customBadge,
                headerTitle: header?.querySelector('.rzp-header-title')?.innerText,
                emiStarting: widget.querySelector('#rzp-starting-emi-val')?.innerText
            };
        })()`,
        returnByValue: true
    });

    console.log("PDP Evaluation:\n", JSON.stringify(pdpEval.result.value, null, 2));

    const pdpScreenshot = await pageClient.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/vexnol_pdp_no_standalone_badge.png', Buffer.from(pdpScreenshot.data, 'base64'));
    console.log("Saved: vexnol_pdp_no_standalone_badge.png");

    await browserClient.send('Target.closeTarget', { targetId });
    browserClient.close();
    console.log("Verification finished successfully!");
}

main().catch(err => {
    console.error("Verification failed:", err);
    process.exit(1);
});
