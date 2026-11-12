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

    await pageClient.send('Emulation.setDeviceMetricsOverride', {
        width: 1280,
        height: 1500,
        deviceScaleFactor: 2,
        mobile: false
    });

    const targetUrl = 'http://127.0.0.1:8000/cart';
    console.log(`Navigating to ${targetUrl}...`);
    await pageClient.send('Page.navigate', { url: targetUrl });
    await new Promise(r => setTimeout(r, 2500));

    // Scroll to cart total / checkout button
    await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var b = document.querySelector('.razorpay-cart-emi-alert');
            if (b) b.scrollIntoView({ behavior: 'instant', block: 'center' });
            else {
                var btn = document.querySelector('.checkout-button, .proceed-to-checkout');
                if (btn) btn.scrollIntoView({ behavior: 'instant', block: 'center' });
            }
        })()`
    });
    await new Promise(r => setTimeout(r, 500));

    const checkRes = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var banner = document.querySelector('.razorpay-cart-emi-alert');
            if (!banner) return { found: false };
            var parent = banner.parentElement ? banner.parentElement.className : '';
            var next = banner.nextElementSibling ? banner.nextElementSibling.className : '';
            return {
                found: true,
                text: banner.innerText.replace(/\\n/g, ' '),
                parentClass: parent,
                nextSiblingClass: next
            };
        })()`,
        returnByValue: true
    });

    console.log("Cart Banner Status:", JSON.stringify(checkRes.result.value));

    const screenshot = await pageClient.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/cart_universal_test.png', Buffer.from(screenshot.data, 'base64'));
    console.log("Captured cart_universal_test.png");

    await browserClient.send('Target.closeTarget', { targetId });
    pageClient.close();
    browserClient.close();
}

main().catch(console.error);
