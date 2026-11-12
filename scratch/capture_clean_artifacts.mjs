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
    const versionRes = await fetch('http://127.0.0.1:9222/json/version');
    const version = await versionRes.json();
    const browserClient = await createCdpClient(version.webSocketDebuggerUrl);
    const { targetId } = await browserClient.send('Target.createTarget', { url: 'about:blank' });
    const targetWsUrl = `ws://127.0.0.1:9222/devtools/page/${targetId}`;
    const pageClient = await createCdpClient(targetWsUrl);

    await pageClient.send('Page.enable');
    await pageClient.send('DOM.enable');
    await pageClient.send('Runtime.enable');
    await pageClient.send('Network.enable');
    await pageClient.send('Network.setCacheDisabled', { cacheDisabled: true });

    await pageClient.send('Emulation.setDeviceMetricsOverride', {
        width: 1280,
        height: 1000,
        deviceScaleFactor: 2,
        mobile: false
    });

    const brainDir = '/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2';

    // 1. Catalog Card with Bottom-Left Shining Badge & Two-Line Seller
    console.log("1. Capturing Catalog Card...");
    await pageClient.send('Page.navigate', { url: 'http://127.0.0.1:8000/products?keyword=Vexnol' });
    await new Promise(r => setTimeout(r, 3500));

    const cardClip = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var badge = document.querySelector('.razorpay-easy-emi-catalog-badge');
            var card = badge ? badge.closest('.product-inner, .product-item, .product-card, article') : null;
            if (!card) return null;
            var r = card.getBoundingClientRect();
            return {
                x: r.x,
                y: r.y,
                width: r.width,
                height: r.height,
                scale: 2
            };
        })()`,
        returnByValue: true
    });

    if (cardClip.result.value) {
        const shot1 = await pageClient.send('Page.captureScreenshot', {
            format: 'png',
            clip: cardClip.result.value
        });
        fs.writeFileSync(`${brainDir}/catalog_card_bottom_left_badge_and_seller_aligned.png`, Buffer.from(shot1.data, 'base64'));
        console.log("Saved: catalog_card_bottom_left_badge_and_seller_aligned.png");
    }

    // 2. Also capture the 5 cards in a row to verify uniform seller alignment across all vendors
    const gridClip = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var grid = document.querySelector('.products-listing, .product-list, .row.row-cols-2, .row.row-cols-lg-5');
            if (!grid) {
                var firstCard = document.querySelector('.product-inner, .product-item, .product-card');
                grid = firstCard ? firstCard.closest('.row') : null;
            }
            if (!grid) return null;
            var r = grid.getBoundingClientRect();
            return {
                x: r.x,
                y: r.y,
                width: r.width,
                height: Math.min(r.height, 600),
                scale: 2
            };
        })()`,
        returnByValue: true
    });

    if (gridClip.result.value) {
        const shot2 = await pageClient.send('Page.captureScreenshot', {
            format: 'png',
            clip: gridClip.result.value
        });
        fs.writeFileSync(`${brainDir}/catalog_seller_badges_uniform_grid.png`, Buffer.from(shot2.data, 'base64'));
        console.log("Saved: catalog_seller_badges_uniform_grid.png");
    }

    // 3. PDP Widget (No standalone badge)
    console.log("2. Capturing PDP Widget...");
    await pageClient.send('Page.navigate', { url: 'http://127.0.0.1:8000/products/thresher-main-shaft-bearing-bracket-with-openable-lid' });
    await new Promise(r => setTimeout(r, 3500));

    const pdpClip = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var widget = document.querySelector('.razorpay-easy-emi-container');
            if (!widget) return null;
            var r = widget.getBoundingClientRect();
            return {
                x: r.x - 10,
                y: Math.max(0, r.y - 60),
                width: r.width + 20,
                height: r.height + 80,
                scale: 2
            };
        })()`,
        returnByValue: true
    });

    if (pdpClip.result.value) {
        const shot3 = await pageClient.send('Page.captureScreenshot', {
            format: 'png',
            clip: pdpClip.result.value
        });
        fs.writeFileSync(`${brainDir}/pdp_widget_clean_no_standalone_badge.png`, Buffer.from(shot3.data, 'base64'));
        console.log("Saved: pdp_widget_clean_no_standalone_badge.png");
    }

    // 4. Admin Metabox Rendered Verification
    console.log("3. Capturing Admin Metabox HTML render...");
    await pageClient.send('Page.navigate', { url: 'http://127.0.0.1:8000/scratch/test_metabox_page.html' });
    await new Promise(r => setTimeout(r, 2000));

    const metaClip = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var box = document.getElementById('razorpay_easy_emi_meta_box');
            if (!box) return null;
            var r = box.getBoundingClientRect();
            return {
                x: r.x,
                y: r.y,
                width: r.width,
                height: r.height,
                scale: 2
            };
        })()`,
        returnByValue: true
    });

    if (metaClip.result.value) {
        const shot4 = await pageClient.send('Page.captureScreenshot', {
            format: 'png',
            clip: metaClip.result.value
        });
        fs.writeFileSync(`${brainDir}/admin_metabox_polished_clean.png`, Buffer.from(shot4.data, 'base64'));
        console.log("Saved: admin_metabox_polished_clean.png");
    }
    await browserClient.send('Target.closeTarget', { targetId });
    browserClient.close();
    console.log("Done!");
}

main().catch(console.error);
