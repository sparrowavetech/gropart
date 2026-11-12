import fs from 'fs';
import http from 'http';

// Helper to send CDP command over WebSocket
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

    // Read cookie file to get session cookies
    const cookieText = fs.readFileSync('/Applications/XAMPP/xamppfiles/htdocs/gropart/scratch/test_cookie.txt', 'utf8');
    const cookies = [];
    for (const line of cookieText.split('\n')) {
        const parts = line.split('\t');
        if (parts.length >= 7) {
            cookies.push({
                name: parts[5].trim(),
                value: parts[6].trim(),
                domain: '127.0.0.1',
                path: '/',
            });
        }
    }
    console.log(`Found ${cookies.length} session cookies.`);

    // Read checkout URL
    const checkoutUrl = fs.readFileSync('/Applications/XAMPP/xamppfiles/htdocs/gropart/scratch/last_checkout_url.txt', 'utf8').trim();
    console.log("Checkout URL:", checkoutUrl);

    // 1. Create a new Page Target
    const { targetId } = await browserClient.send('Target.createTarget', { url: 'about:blank' });
    const targetWsUrl = `ws://127.0.0.1:9222/devtools/page/${targetId}`;
    const page = await createCdpClient(targetWsUrl);

    await page.send('Page.enable');
    await page.send('Network.enable');
    await page.send('Runtime.enable');
    await page.send('Emulation.setDeviceMetricsOverride', {
        width: 1280,
        height: 1024,
        deviceScaleFactor: 1,
        mobile: false
    });

    // Set cookies into browser
    for (const c of cookies) {
        await page.send('Network.setCookie', c);
    }
    console.log("Cookies injected into browser session.");

    // --- STEP 1: CART PAGE TEST ---
    console.log("\n=== STEP 1: Navigating to Cart Page ===");
    await page.send('Page.navigate', { url: 'http://127.0.0.1:8000/cart' });
    await new Promise(r => setTimeout(r, 2500));

    // Capture Cart Screenshot
    const cartScreenshot = await page.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Applications/XAMPP/xamppfiles/htdocs/gropart/scratch/cart_page_screenshot.png', Buffer.from(cartScreenshot.data, 'base64'));
    console.log("Saved: scratch/cart_page_screenshot.png");

    // Check if Cart EMI Alert exists in DOM
    const cartAlertCheck = await page.send('Runtime.evaluate', {
        expression: "!!document.querySelector('.alert.alert-info .ti-credit-card')"
    });
    console.log("Cart EMI Banner Rendered in Browser:", cartAlertCheck.result.value);

    // --- STEP 2: CHECKOUT PAGE TEST ---
    console.log("\n=== STEP 2: Navigating to Checkout Page ===");
    await page.send('Page.navigate', { url: checkoutUrl });
    await new Promise(r => setTimeout(r, 3000));

    // Check if the EMI card is docked under Razorpay
    const emiCardCheck = await page.send('Runtime.evaluate', {
        expression: "!!document.querySelector('.razorpay-checkout-emi-card')"
    });
    console.log("Checkout EMI Card Docked under Razorpay:", emiCardCheck.result.value);

    // Check if Razorpay radio is checked
    const rzpRadioCheck = await page.send('Runtime.evaluate', {
        expression: "document.querySelector('input[name=\"payment_method\"][value=\"razorpay\"]').checked"
    });
    console.log("Razorpay Radio Checked:", rzpRadioCheck.result.value);

    // Capture Checkout Screenshot
    const checkoutScreenshot = await page.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Applications/XAMPP/xamppfiles/htdocs/gropart/scratch/checkout_page_screenshot.png', Buffer.from(checkoutScreenshot.data, 'base64'));
    console.log("Saved: scratch/checkout_page_screenshot.png");

    // --- STEP 3: ATTEMPT PAYMENT & VERIFY RAZORPAY MODAL (NO MONEY PAID) ---
    console.log("\n=== STEP 3: Form Fill & Launch Razorpay Modal ===");

    // Fill customer form fields
    await page.send('Runtime.evaluate', {
        expression: `
            (function() {
                function setVal(sel, val) {
                    var el = document.querySelector(sel);
                    if (el) {
                        el.value = val;
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
                setVal('#address_name', 'Test Customer');
                setVal('#address_phone', '9876543210');
                setVal('#address_email', 'test@gropart.com');
                setVal('#address_address', '123 Test Street');
                setVal('#address_city', 'Jaipur');
                setVal('#address_zip_code', '302001');

                // Select Razorpay if not selected
                var rzp = document.querySelector('input[name="payment_method"][value="razorpay"]');
                if (rzp) {
                    rzp.checked = true;
                    rzp.dispatchEvent(new Event('change', { bubbles: true }));
                }

                // Check pay via emi checkbox
                var emiChk = document.querySelector('#chk_pay_via_emi');
                if (emiChk) emiChk.checked = true;

                return true;
            })()
        `
    });

    console.log("Customer details filled in checkout form.");
    await new Promise(r => setTimeout(r, 1000));

    // Submit the checkout form to trigger Razorpay
    console.log("Submitting checkout form to launch Razorpay...");
    await page.send('Runtime.evaluate', {
        expression: `
            (function() {
                var btn = document.querySelector('.payment-checkout-btn') || document.querySelector('button[type="submit"]');
                if (btn) btn.click();
            })()
        `
    });

    // Wait for Razorpay modal iframe to appear (up to 6 seconds)
    console.log("Waiting for Razorpay Modal iframe...");
    let modalLoaded = false;
    for (let i = 0; i < 12; i++) {
        await new Promise(r => setTimeout(r, 500));
        const iframeCheck = await page.send('Runtime.evaluate', {
            expression: "!!document.querySelector('iframe.razorpay-checkout-frame') || !!document.querySelector('iframe[src*=\"razorpay\"]')"
        });
        if (iframeCheck.result.value) {
            modalLoaded = true;
            console.log(`Razorpay modal iframe detected after ${(i + 1) * 500}ms!`);
            break;
        }
    }

    // Wait another 2 seconds for Razorpay modal contents to paint
    await new Promise(r => setTimeout(r, 2000));

    // Capture screenshot of the modal
    const modalScreenshot = await page.send('Page.captureScreenshot', { format: 'png' });
    fs.writeFileSync('/Applications/XAMPP/xamppfiles/htdocs/gropart/scratch/razorpay_modal_screenshot.png', Buffer.from(modalScreenshot.data, 'base64'));
    console.log("Saved: scratch/razorpay_modal_screenshot.png");

    // Close the modal / target cleanly without paying
    await browserClient.send('Target.closeTarget', { targetId });
    browserClient.close();
    console.log("\n=== COMPLETED TEST CLEANLY (ZERO PAYMENT TRANSACTED) ===");
}

main().catch(err => {
    console.error("Test failed:", err);
    process.exit(1);
});

