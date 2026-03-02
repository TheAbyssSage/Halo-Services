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

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

Stripe::setApiKey(STRIPE_SECRET_KEY);

$sessionId  = $_GET['session_id'] ?? null;
$free       = isset($_GET['free']);   // cart had only free items
$testMode   = isset($_GET['test']);   // bypassed via TEST_MODE flag
$paid       = false;
$error      = null;

if ($free || $testMode) {
    $paid = true;
} else {
    try {
        if (!$sessionId) {
            throw new RuntimeException('Missing Stripe session ID.');
        }
        $session = StripeSession::retrieve($sessionId);
        if ($session->payment_status === 'paid') {
            $paid = true;
        } else {
            $error = 'Payment not completed yet. Status: ' . $session->payment_status;
        }
    } catch (Exception $e) {
        $error = 'Stripe error: ' . $e->getMessage();
    }
}

// Collect downloadable files before clearing cart
$cart          = $_SESSION['cart'] ?? [];
$downloadFiles = [];

if ($paid && !empty($cart)) {
    foreach ($cart as $item) {
        if (is_array($item) && ($item['type'] ?? '') === 'certificate') {
            $relative = $item['file_path'] ?? null;
            if ($relative) {
                $publicPath = __DIR__ . '/' . $relative;
                if (file_exists($publicPath)) {
                    $downloadFiles[] = [
                        'label' => $item['display'] ?? 'Certificate',
                        'href'  => $relative,
                    ];
                }
            }
        }
    }
    $_SESSION['cart'] = [];
}

// Render in layout
ob_start();
?>
<div class="cert-hero">
    <div class="cert-hero-icon">☑</div>
    <h1>Order <span>complete</span></h1>
    <p class="cert-hero-sub">
        Your request has been processed by the Hex &amp; Halo bureau. If the angels approved payment,
        your freshly minted celestial paperwork is ready to download.
    </p>
    <div class="cert-hero-meta">
        <?php if ($testMode): ?>
            <div class="cert-pill">test mode – no real charge</div>
        <?php endif; ?>
        <?php if ($free): ?>
            <div class="cert-pill">free order – blessing only</div>
        <?php endif; ?>
        <div class="cert-pill">pdf certificates</div>
        <div class="cert-pill">qr‑tagged downloads</div>
    </div>
</div>

<?php if ($testMode): ?>
    <div class="cert-lore">
        <h2>Test run acknowledged</h2>
        <p>
            This was a dry run through the celestial checkout systems. No mortal payment methods were
            touched; the angels simply pretended very convincingly.
        </p>
        <p>
            To go live, set <code>TEST_MODE = false</code> in <code>config.php</code> and the bureau
            will start talking to real Stripe sessions instead of imaginary ones.
        </p>
        <div class="alert alert-warning mt-2 mb-0">
            <strong>⚠ Test mode is on.</strong>
            Payment was skipped. Set <code>TEST_MODE = false</code> in <code>config.php</code> to go live.
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="cert-lore">
        <h2>Something went sideways</h2>
        <p>
            The payment status could not be cleanly confirmed. Sometimes Stripe blinks, sometimes
            the bureaucracy does. Either way, no new paperwork has been filed… yet.
        </p>
        <div class="alert alert-danger mt-2">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <a href="cart.php" class="btn btn-secondary btn-sm mt-2">Back to cart</a>
    </div>

<?php elseif ($paid): ?>

    <div class="cert-lore">
        <h2>Your celestial paperwork is ready</h2>
        <p>
            The transaction cleared, the ledgers updated, and at least one many‑eyed auditor nodded
            in approval. Your certificates are now ready to be downloaded and framed, printed,
            or hoarded in a very organised folder.
        </p>
        <div class="alert alert-success mt-2">
            Payment received. Your celestial paperwork is ready below.
        </div>

        <?php if (!empty($downloadFiles)): ?>
            <p class="mt-3">
                Downloads should start automatically in a moment. If your browser is feeling shy,
                you can also grab them manually here:
            </p>
            <ul class="mb-3">
                <?php foreach ($downloadFiles as $i => $file): ?>
                    <li>
                        <a id="cert-link-<?php echo $i; ?>"
                            href="<?php echo htmlspecialchars($file['href']); ?>"
                            download>
                            <?php echo htmlspecialchars($file['label']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <script>
                window.addEventListener('load', function() {
                    <?php foreach ($downloadFiles as $i => $file): ?>
                            (function() {
                                var link = document.getElementById('cert-link-<?php echo $i; ?>');
                                if (link) {
                                    // Stagger downloads slightly so browsers don't block them
                                    setTimeout(function() {
                                        link.click();
                                    }, <?php echo $i * 600; ?>);
                                }
                            })();
                    <?php endforeach; ?>
                });
            </script>
        <?php else: ?>
            <p class="mt-3">
                No downloadable certificates were found for this order. If you were expecting
                something with halos and fine print, you may want to check your cart and try again.
            </p>
        <?php endif; ?>

        <a href="index.php" class="btn btn-dark btn-sm mt-2">Back to home ✦</a>
    </div>

<?php else: ?>

    <div class="cert-lore">
        <h2>Payment status unclear</h2>
        <p>
            Stripe didn&apos;t confirm a completed payment, so the angels are holding your paperwork
            in a pending state. No certificates were released this round.
        </p>
        <div class="alert alert-warning mt-2">
            Payment status could not be confirmed.
        </div>
        <a href="cart.php" class="btn btn-secondary btn-sm mt-2">Back to cart</a>
    </div>

<?php endif; ?>

<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Order complete';
$activePage = 'shop';
include __DIR__ . '/layout.php';
