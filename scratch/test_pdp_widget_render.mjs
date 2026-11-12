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
    console.log("Browser:", version.Browser);

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
        height: 1000,
        deviceScaleFactor: 2,
        mobile: false
    });

    const targetUrl = 'http://127.0.0.1:8000/products/thresher-main-shaft-bearing-bracket-with-openable-lid';
    console.log(`Navigating to ${targetUrl}...`);
    await pageClient.send('Page.navigate', { url: targetUrl });

    // Wait for load event
    await new Promise(r => setTimeout(r, 3000));

    // Evaluate if widget is present
    const evalRes = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var widget = document.querySelector('.razorpay-easy-emi-container');
            if (!widget) return { found: false };
            var startingVal = widget.querySelector('#rzp-starting-emi-val')?.innerText;
            var savingsVal = widget.querySelector('#rzp-savings-val')?.innerText;
            var header = widget.querySelector('.rzp-header-title')?.innerText;
            return {
                found: true,
                startingVal: startingVal,
                savingsVal: savingsVal,
                header: header
            };
        })()`,
        returnByValue: true
    });

    console.log("Widget Evaluation:", JSON.stringify(evalRes.result.value));

    // Capture screenshot of the widget on page
    const screenshot1 = await pageClient.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/pdp_new_widget.png', Buffer.from(screenshot1.data, 'base64'));
    console.log("Captured pdp_new_widget.png");

    // Click 'View plans' button
    await pageClient.send('Runtime.evaluate', {
        expression: `document.querySelector('.rzp-btn-view-plans')?.click()`
    });
    await new Promise(r => setTimeout(r, 1000));

    // Capture modal screenshot
    const screenshot2 = await pageClient.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/pdp_plans_modal.png', Buffer.from(screenshot2.data, 'base64'));
    console.log("Captured pdp_plans_modal.png");

    // Close plans modal, open offers modal
    await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var closeBtn = document.querySelector('#razorpay-emi-plans-modal .btn-close');
            if (closeBtn) closeBtn.click();
        })()`
    });
    await new Promise(r => setTimeout(r, 600));

    await pageClient.send('Runtime.evaluate', {
        expression: `document.querySelector('.rzp-btn-view-offers')?.click()`
    });
    await new Promise(r => setTimeout(r, 1000));

    // Capture offers modal screenshot
    const screenshot3 = await pageClient.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/pdp_offers_modal.png', Buffer.from(screenshot3.data, 'base64'));
    console.log("Captured pdp_offers_modal.png");

    // Cleanup target
    await browserClient.send('Target.closeTarget', { targetId });
    pageClient.close();
    browserClient.close();
    console.log("Done testing PDP widget!");
}

main().catch(err => {
    console.error("Test failed:", err);
    process.exit(1);
});

