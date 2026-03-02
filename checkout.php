<?php
session_start();

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

// Try both naming conventions for Stripe config
if (file_exists(__DIR__ . '/config.stripe.php')) {
    require __DIR__ . '/config.stripe.php';
} elseif (file_exists(__DIR__ . '/config_stripe.php')) {
    require __DIR__ . '/config_stripe.php';
} elseif (file_exists(__DIR__ . '/../config.stripe.php')) {
    require __DIR__ . '/../config.stripe.php';
} else {
    die('Stripe config not found. Expected config.stripe.php or config_stripe.php in public/ or project root.');
}

// ── TEST MODE: skip Stripe entirely ──────────────────────────────────────────
if (TEST_MODE) {
    header('Location: checkout_success.php?test=1');
    exit;
}
// ─────────────────────────────────────────────────────────────────────────────

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

Stripe::setApiKey(STRIPE_SECRET_KEY);

$cart  = $_SESSION['cart'] ?? [];
$error = null;

if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// Build Stripe line items
$lineItems = [];

foreach ($cart as $key => $item) {
    // Certificate items (objects with type = 'certificate')
    if (is_array($item) && ($item['type'] ?? '') === 'certificate') {
        $price = (float) ($item['price'] ?? 0);
        if ($price <= 0) continue; // free items skip Stripe

        $lineItems[] = [
            'price_data' => [
                'currency'     => STRIPE_CURRENCY,
                'unit_amount'  => (int) round($price * 100),
                'product_data' => [
                    'name' => $item['display'] ?? 'Certificate',
                ],
            ],
            'quantity' => 1,
        ];
        continue;
    }

    // Normal products (key = productId, value = qty)
    $productId = (int) $key;
    $qty       = (int) $item;
    if (!isset($productsById[$productId]) || $qty < 1) continue;

    $product = $productsById[$productId];
    $lineItems[] = [
        'price_data' => [
            'currency'     => STRIPE_CURRENCY,
            'unit_amount'  => (int) round($product['price'] * 100),
            'product_data' => [
                'name' => $product['name'],
            ],
        ],
        'quantity' => $qty,
    ];
}

if (empty($lineItems)) {
    // Cart only has free items – skip Stripe, go straight to success
    header('Location: checkout_success.php?free=1');
    exit;
}

try {
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
        . '://' . $_SERVER['HTTP_HOST']
        . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

    $session = StripeSession::create([
        'payment_method_types' => ['card'],
        'line_items'           => $lineItems,
        'mode'                 => 'payment',
        'success_url'          => $baseUrl . '/checkout_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'           => $baseUrl . '/cart.php',
    ]);

    header('Location: ' . $session->url);
    exit;
} catch (Exception $e) {
    $error = 'Stripe error: ' . htmlspecialchars($e->getMessage());
}

// If we get here, render an error page in layout
ob_start();
?>
<h1>Checkout error</h1>
<div class="alert alert-danger"><?php echo $error; ?></div>
<a href="cart.php" class="btn btn-dark btn-sm">Back to cart</a>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Checkout error';
$activePage = 'cart';
include __DIR__ . '/layout.php';
