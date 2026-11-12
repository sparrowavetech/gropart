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

    // Disable cache so fresh CSS is loaded
    await pageClient.send('Network.setCacheDisabled', { cacheDisabled: true });

    await pageClient.send('Page.navigate', { url: 'http://127.0.0.1:8000/products?keyword=Vexnol' });
    await new Promise(r => setTimeout(r, 3000));

    const evalRes = await pageClient.send('Runtime.evaluate', {
        expression: `(function() {
            var s = document.querySelector('.sold-by-meta');
            if (!s) return { found: false };
            var style = window.getComputedStyle(s);
            var link = s.querySelector('a');
            var badgesWrap = s.querySelector('.vendor-badges-wrap');
            var verifiedLabel = s.querySelector('.vendor-verified-label');
            var shopType = s.querySelector('.vendor-shop-type-badge');

            // Also check link style and badgesWrap style
            var linkTag = document.querySelector('link[href*="vendor-badge.css"]');

            return {
                found: true,
                hasLinkTag: !!linkTag,
                linkHref: linkTag ? linkTag.href : null,
                display: style.display,
                flexDirection: style.flexDirection,
                linkDisplay: link ? window.getComputedStyle(link).display : null,
                hasBadgesWrap: !!badgesWrap,
                badgesWrapDisplay: badgesWrap ? window.getComputedStyle(badgesWrap).display : null,
                innerHtml: s.innerHTML
            };
        })()`,
        returnByValue: true
    });

    console.log("CSS Check with cache disabled:", JSON.stringify(evalRes.result.value, null, 2));

    await browserClient.send('Target.closeTarget', { targetId });
    browserClient.close();
}

main().catch(console.error);
