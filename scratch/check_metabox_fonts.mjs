import fs from 'fs';

async function test() {
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
        const { targetId } = await call('Target.createTarget', { url: 'http://127.0.0.1:8000/scratch/test_metabox_page.html' });
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
            await pCall('Runtime.enable');
            await new Promise(r => setTimeout(r, 1000));

            const fontAudit = await pCall('Runtime.evaluate', {
                expression: `(function() {
                    var titles = document.querySelectorAll('.rzp-meta-title');
                    var subtitles = document.querySelectorAll('.rzp-meta-subtitle');

                    var titleDetails = [];
                    titles.forEach(function(t) {
                        var st = window.getComputedStyle(t);
                        titleDetails.push({
                            text: t.innerText.trim(),
                            fontSize: st.fontSize,
                            fontWeight: st.fontWeight,
                            lineHeight: st.lineHeight
                        });
                    });

                    var subtitleDetails = [];
                    subtitles.forEach(function(s) {
                        var st = window.getComputedStyle(s);
                        subtitleDetails.push({
                            text: s.innerText.trim(),
                            fontSize: st.fontSize,
                            color: st.color,
                            lineHeight: st.lineHeight
                        });
                    });

                    return {
                        titlesCount: titles.length,
                        titleDetails: titleDetails,
                        subtitlesCount: subtitles.length,
                        subtitleDetails: subtitleDetails
                    };
                })()`,
                returnByValue: true
            });

            console.log("Font Audit Results:\n", JSON.stringify(fontAudit.result.value, null, 2));

            const shot = await pCall('Page.captureScreenshot', { format: 'png' });
            fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/admin_metabox_typography_perfect.png', Buffer.from(shot.data, 'base64'));
            console.log("Saved admin_metabox_typography_perfect.png");

            await call('Target.closeTarget', { targetId });
            ws.close();
        };
    };
}

test().catch(console.error);

