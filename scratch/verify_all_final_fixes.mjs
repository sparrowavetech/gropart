import fs from 'fs';

async function main() {
    const versionRes = await fetch('http://127.0.0.1:9222/json/version');
    const version = await versionRes.json();
    const ws = new WebSocket(version.webSocketDebuggerUrl);
    let id = 1;
    const call = (method, params = {}) => new Promise((resolve) => {
        const curId = id++;
        const handler = (event) => {
            const msg = JSON.parse(event.data);
            if (msg.id === curId) {
                ws.removeEventListener('message', handler);
                resolve(msg.result);
            }
        };
        ws.addEventListener('message', handler);
        ws.send(JSON.stringify({ id: curId, method, params }));
    });

    ws.onopen = async () => {
        const { targetId } = await call('Target.createTarget', { url: 'about:blank' });
        const pageWs = new WebSocket('ws://127.0.0.1:9222/devtools/page/' + targetId);
        let pId = 1;
        const pCall = (method, params = {}) => new Promise((resolve) => {
            const curId = pId++;
            const handler = (event) => {
                const msg = JSON.parse(event.data);
                if (msg.id === curId) {
                    pageWs.removeEventListener('message', handler);
                    resolve(msg.result);
                }
            };
            pageWs.addEventListener('message', handler);
            pageWs.send(JSON.stringify({ id: curId, method, params }));
        });

        pageWs.onopen = async () => {
            await pCall('Page.enable');
            await pCall('DOM.enable');
            await pCall('Runtime.enable');
            await pCall('Network.enable');
            await pCall('Network.setCacheDisabled', { cacheDisabled: true });
            await pCall('Emulation.setDeviceMetricsOverride', { width: 1280, height: 1100, deviceScaleFactor: 2, mobile: false });

            // 1. Audit Admin Metabox Typography
            console.log("\n--- TEST 1: Admin Metabox Typography ---");
            await pCall('Page.navigate', { url: 'http://127.0.0.1:8000/test_metabox_page.html' });
            await new Promise(r => setTimeout(r, 2000));

            const typoAudit = await pCall('Runtime.evaluate', {
                expression: `(function() {
                    var titles = document.querySelectorAll('.rzp-meta-title');
                    var subtitles = document.querySelectorAll('.rzp-meta-subtitle');

                    var resTitles = [];
                    titles.forEach(function(t) {
                        var st = window.getComputedStyle(t);
                        resTitles.push({
                            label: t.innerText.trim().split('\\n')[0],
                            fontSize: st.fontSize,
                            fontWeight: st.fontWeight,
                            lineHeight: st.lineHeight,
                            color: st.color
                        });
                    });

                    var resSubtitles = [];
                    subtitles.forEach(function(s) {
                        var st = window.getComputedStyle(s);
                        resSubtitles.push({
                            label: s.innerText.trim().slice(0, 35) + '...',
                            fontSize: st.fontSize,
                            color: st.color,
                            lineHeight: st.lineHeight
                        });
                    });

                    return {
                        totalTitles: titles.length,
                        titles: resTitles,
                        totalSubtitles: subtitles.length,
                        subtitles: resSubtitles
                    };
                })()`,
                returnByValue: true
            });

            console.log("Metabox Typography Audit:\n", JSON.stringify(typoAudit.result.value, null, 2));

            const shotMetabox = await pCall('Page.captureScreenshot', { format: 'png' });
            fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/admin_metabox_perfect_typography.png', Buffer.from(shotMetabox.data, 'base64'));
            console.log("Saved: admin_metabox_perfect_typography.png");

            // 2. Audit Catalog Card Badge
            console.log("\n--- TEST 2: Catalog Card Badge ---");
            await pCall('Page.navigate', { url: 'http://127.0.0.1:8000/products?keyword=Vexnol' });
            await new Promise(r => setTimeout(r, 3000));

            const badgeAudit = await pCall('Runtime.evaluate', {
                expression: `(function() {
                    var badge = document.querySelector('.razorpay-easy-emi-catalog-badge');
                    if (!badge) return { found: false };
                    var pill = badge.querySelector('.rzp-badge-pill');
                    var text = badge.querySelector('.rzp-badge-text');
                    var bStyle = window.getComputedStyle(badge);
                    var pStyle = pill ? window.getComputedStyle(pill) : null;
                    var card = badge.closest('.product-inner, .product-item, .product-card');
                    var thumb = card ? card.querySelector('.product-thumbnail') : null;

                    var bRect = badge.getBoundingClientRect();
                    var tRect = thumb ? thumb.getBoundingClientRect() : null;

                    return {
                        found: true,
                        badgeText: text ? text.innerText.trim() : null,
                        hasEllipsis: text ? (text.scrollWidth > text.clientWidth) : false,
                        bottom: bStyle.bottom,
                        left: bStyle.left,
                        fontSize: pStyle ? pStyle.fontSize : null,
                        padding: pStyle ? pStyle.padding : null,
                        pillWidth: bRect.width,
                        pillHeight: bRect.height,
                        thumbWidth: tRect ? tRect.width : null,
                        backgroundColor: pStyle ? pStyle.backgroundColor : null,
                        border: pStyle ? pStyle.border : null,
                        boxShadow: pStyle ? pStyle.boxShadow : null
                    };
                })()`,
                returnByValue: true
            });

            console.log("Catalog Badge Audit:\n", JSON.stringify(badgeAudit.result.value, null, 2));

            const shotCard = await pCall('Page.captureScreenshot', { format: 'png' });
            fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/catalog_badge_clean_refined.png', Buffer.from(shotCard.data, 'base64'));
            console.log("Saved: catalog_badge_clean_refined.png");

            await call('Target.closeTarget', { targetId });
            ws.close();
            console.log("\nAll audits complete!");
        };
    };
}

main().catch(console.error);
