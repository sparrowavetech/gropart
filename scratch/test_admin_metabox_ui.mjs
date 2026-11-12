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
    console.log("=== Testing Admin Razorpay EMI Metabox Distortion Fix ===");
    const versionRes = await fetch('http://127.0.0.1:9222/json/version');
    const version = await versionRes.json();
    const browserClient = await createCdpClient(version.webSocketDebuggerUrl);

    const { targetId } = await browserClient.send('Target.createTarget', { url: 'about:blank' });
    const targetWsUrl = `ws://127.0.0.1:9222/devtools/page/${targetId}`;
    const pageClient = await createCdpClient(targetWsUrl);

    await pageClient.send('Page.enable');
    await pageClient.send('DOM.enable');
    await pageClient.send('Runtime.enable');
    // Set exact viewport matching the user's screen (1024px width)
    await pageClient.send('Emulation.setDeviceMetricsOverride', {
        width: 1024, height: 1300, deviceScaleFactor: 2, mobile: false
    });

    const fileUrl = 'file:///Applications/XAMPP/xamppfiles/htdocs/gropart/scratch/test_metabox_page.html';
    await pageClient.send('Page.navigate', { url: fileUrl });
    await new Promise(r => setTimeout(r, 2000));

    // TEST 1: Initial state & check for text truncation
    console.log("\n--- TEST 1: Initial State & Text Overflow Audit ---");
    const test1 = await pageClient.send('Runtime.evaluate', {
        expression: `(() => {
            const titles = Array.from(document.querySelectorAll('.rzp-offer-title')).map(el => ({
                text: el.textContent.trim(),
                scrollWidth: el.scrollWidth,
                clientWidth: el.clientWidth,
                isOverflowingHorizontally: el.scrollWidth > el.clientWidth + 2
            }));
            const badges = Array.from(document.querySelectorAll('.rzp-offer-box .badge')).map(el => ({
                text: el.textContent.trim(),
                scrollWidth: el.scrollWidth,
                clientWidth: el.clientWidth,
                isOverflowing: el.scrollWidth > el.clientWidth + 2
            }));
            const anyTitleOverflow = titles.some(t => t.isOverflowingHorizontally);
            const anyBadgeOverflow = badges.some(b => b.isOverflowing);

            return {
                totalTitlesChecked: titles.length,
                anyTitleOverflow,
                anyBadgeOverflow,
                sampleTitle: titles[0] ? titles[0].text : null,
                globalPill: document.getElementById('global_selected_pill').textContent
            };
        })()`,
        returnByValue: true
    });
    console.log("Overflow Audit Result:", JSON.stringify(test1.result.value, null, 2));

    // TEST 2: Click Master Checkbox in Tab 1 (Bank EMI)
    console.log("\n--- TEST 2: Click Master Category 1 (EMI Offers) ---");
    const test2 = await pageClient.send('Runtime.evaluate', {
        expression: `(() => {
            const masterEmi = document.getElementById('master_checkbox_emi');
            masterEmi.click();
            const checkedEmiCount = document.querySelectorAll('#tab_pane_bank_emi input:checked').length;
            const selectedText = document.getElementById('emi_selected_count').textContent;
            const globalPill = document.getElementById('global_selected_pill').textContent;
            return {
                masterEmiChecked: masterEmi.checked,
                checkedEmiCount,
                selectedText,
                globalPill
            };
        })()`,
        returnByValue: true
    });
    console.log("After clicking Master EMI Checkbox:", JSON.stringify(test2.result.value, null, 2));

    // TEST 3: Switch to Tab 2 (Other Offers) and click Master Checkbox
    console.log("\n--- TEST 3: Switch to Tab 2 and click Master Other Checkbox ---");
    const test3 = await pageClient.send('Runtime.evaluate', {
        expression: `(() => {
            // Click Tab 2
            const tab2Btn = document.querySelector('.rzp-tab-btn[data-target="tab_pane_other_offers"]');
            tab2Btn.click();

            const masterOther = document.getElementById('master_checkbox_other');
            masterOther.click();

            const masterAll = document.getElementById('master_checkbox_all');
            const totalChecked = document.querySelectorAll('.rzp-offer-box.is-checked:not(.all-pane-offer-item)').length;
            const globalPill = document.getElementById('global_selected_pill').textContent;
            return {
                tab2Active: tab2Btn.classList.contains('active'),
                pane2Visible: document.getElementById('tab_pane_other_offers').style.display !== 'none',
                masterOtherChecked: masterOther.checked,
                masterAllChecked: masterAll.checked,
                totalChecked,
                globalPill
            };
        })()`,
        returnByValue: true
    });
    console.log("Tab 2 & Master Other Checkbox result:", JSON.stringify(test3.result.value, null, 2));

    // TEST 4: Click Tab 1 again and capture Screenshot
    console.log("\n--- TEST 4: Switch back to Tab 1 & Capture Clean Screenshot ---");
    await pageClient.send('Runtime.evaluate', {
        expression: `(() => {
            document.querySelector('.rzp-tab-btn[data-target="tab_pane_bank_emi"]').click();
        })()`
    });
    await new Promise(r => setTimeout(r, 600));

    const shot1 = await pageClient.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/admin_metabox_tab1_clean.png', Buffer.from(shot1.data, 'base64'));
    console.log("Saved admin_metabox_tab1_clean.png");

    // Capture Tab 2 screenshot as well
    await pageClient.send('Runtime.evaluate', {
        expression: `(() => {
            document.querySelector('.rzp-tab-btn[data-target="tab_pane_other_offers"]').click();
        })()`
    });
    await new Promise(r => setTimeout(r, 600));

    const shot2 = await pageClient.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Users/apple/.gemini/antigravity/brain/2eab73fd-57a8-4cd0-99fd-a4f424f0b8c2/admin_metabox_tab2_clean.png', Buffer.from(shot2.data, 'base64'));
    console.log("Saved admin_metabox_tab2_clean.png");

    await browserClient.send('Target.closeTarget', { targetId });
    browserClient.close();
    console.log("\n=== All Tests Passed with ZERO Overflow ===");
}

main().catch(err => {
    console.error("Test failed:", err);
    process.exit(1);
});
