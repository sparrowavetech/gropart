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
            if (msg.error) reject(msg.error);
            else resolve(msg.result);
        }
    };

    return new Promise((resolveReady, rejectReady) => {
        ws.onopen = () => {
            const send = (method, params = {}) => new Promise((resolve, reject) => {
                const reqId = id++;
                callbacks.set(reqId, { resolve, reject });
                ws.send(JSON.stringify({ id: reqId, method, params }));
            });
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
    await pageClient.send('Emulation.setDeviceMetricsOverride', {
        width: 1280, height: 1000, deviceScaleFactor: 2, mobile: false
    });

    await pageClient.send('Page.navigate', { url: 'http://127.0.0.1:8000/products' });
    await new Promise(r => setTimeout(r, 3000));

    // Scroll to the card
    const cardClip = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var b = document.querySelector('.rzp-catalog-badge-low_cost');
            if (!b) b = document.querySelector('.razorpay-easy-emi-catalog-badge');
            if (!b) return null;
            var card = b.closest('.product-item, .product-inner') || b.parentElement;
            card.scrollIntoView({ behavior: 'instant', block: 'center' });
            var r = card.getBoundingClientRect();
            return { x: r.x, y: r.y, width: r.width, height: r.height, scale: 1 };
        })()`,
        returnByValue: true
    });

    console.log("Card Clip:", JSON.stringify(cardClip.result.value));
    await new Promise(r => setTimeout(r, 500));

    if (cardClip.result.value) {
        const clip = cardClip.result.value;
        const shot = await pageClient.send('Page.captureScreenshot', {
            format: 'png',
            clip: { x: Math.max(0, clip.x - 20), y: Math.max(0, clip.y - 20), width: clip.width + 40, height: clip.height + 40, scale: 2 }
        });
        fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/crop_catalog_badge.png', Buffer.from(shot.data, 'base64'));
        console.log("Saved crop_catalog_badge.png");
    }

    await browserClient.send('Target.closeTarget', { targetId });
    browserClient.close();
}

main().catch(console.error);

