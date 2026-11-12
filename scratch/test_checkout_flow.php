<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cookieFile = '/Applications/XAMPP/xamppfiles/htdocs/gropart/scratch/test_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

function makeRequest($url, $method = 'GET', $data = [], $cookieFile = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)');

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Requested-With: XMLHttpRequest',
            'Accept: application/json',
        ]);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    return ['code' => $httpCode, 'body' => $response, 'url' => $effectiveUrl];
}

echo "=== STEP 1: Fetch PDP & CSRF Token for Product 192 ===\n";
$pdp = makeRequest('http://127.0.0.1:8000/products/thresher-main-shaft-bearing-bracket-with-openable-lid', 'GET', [], $cookieFile);
echo "PDP Response HTTP: " . $pdp['code'] . "\n";

preg_match('/name="_token"\s+value="([^"]+)"/', $pdp['body'], $tokenMatch);
$csrfToken = $tokenMatch[1] ?? null;
echo "CSRF Token: " . ($csrfToken ? substr($csrfToken, 0, 10) . '...' : 'NOT FOUND') . "\n";

echo "\n=== STEP 2: Add Product 192 (₹3,300) to Cart ===\n";
$addToCart = makeRequest('http://127.0.0.1:8000/cart/add-to-cart', 'POST', [
    '_token' => $csrfToken,
    'id' => 192,
    'qty' => 1,
], $cookieFile);
echo "Add To Cart HTTP: " . $addToCart['code'] . "\n";
echo "Response Body: " . substr($addToCart['body'], 0, 200) . "\n";

echo "\n=== STEP 3: Inspect /cart Page ===\n";
$cart = makeRequest('http://127.0.0.1:8000/cart', 'GET', [], $cookieFile);
echo "Cart Page HTTP: " . $cart['code'] . "\n";

$hasCartEmi = str_contains($cart['body'], 'ti ti-credit-card') || str_contains($cart['body'], 'Easy EMI');
echo "Cart EMI Reminder Present: " . ($hasCartEmi ? 'YES' : 'NO') . "\n";
if ($hasCartEmi) {
    preg_match('/<div class="alert alert-info py-2[^>]*>.*?<\/div>/s', $cart['body'], $bannerMatch);
    echo "Cart Banner HTML:\n" . ($bannerMatch[0] ?? 'matched in text') . "\n";
}

// Extract checkout link
preg_match('/href="([^"]*\/checkout\/[a-zA-Z0-9]+)"/', $cart['body'], $checkoutMatch);
$checkoutUrl = $checkoutMatch[1] ?? null;

if ($checkoutUrl && !str_starts_with($checkoutUrl, 'http')) {
    $checkoutUrl = 'http://127.0.0.1:8000' . $checkoutUrl;
}

echo "\nCheckout URL Found: " . ($checkoutUrl ?: 'NOT FOUND') . "\n";

if ($checkoutUrl) {
    echo "\n=== STEP 4: Inspect Checkout Page ===\n";
    $checkout = makeRequest($checkoutUrl, 'GET', [], $cookieFile);
    echo "Checkout Page HTTP: " . $checkout['code'] . "\n";

    $hasCheckoutTemplate = str_contains($checkout['body'], 'razorpay-checkout-emi-template');
    $hasOffers = str_contains($checkout['body'], '__razorpayActiveOffers');
    $hasEmiCard = str_contains($checkout['body'], 'razorpay-checkout-emi-card');
    $hasRazorpayRadio = str_contains($checkout['body'], 'value="razorpay"');

    echo "Razorpay Payment Method Present: " . ($hasRazorpayRadio ? 'YES' : 'NO') . "\n";
    echo "Checkout EMI Template Present: " . ($hasCheckoutTemplate ? 'YES' : 'NO') . "\n";
    echo "Active Offers JS Array Present: " . ($hasOffers ? 'YES' : 'NO') . "\n";
    echo "Checkout EMI Card Present: " . ($hasEmiCard ? 'YES' : 'NO') . "\n";

    file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/gropart/scratch/last_checkout_url.txt', $checkoutUrl);
}
