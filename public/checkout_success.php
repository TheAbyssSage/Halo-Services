<?php
// public/checkout_success.php
session_start();

require __DIR__ . '/../vendor/autoload.php';
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
<h1>Order complete</h1>

<?php if ($testMode): ?>
    <div class="alert alert-warning">
        <strong>⚠ Test mode is on.</strong>
        Payment was skipped. Set <code>TEST_MODE = false</code> in <code>config.php</code> to go live.
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <a href="cart.php" class="btn btn-secondary btn-sm">Back to cart</a>

<?php elseif ($paid): ?>
    <div class="alert alert-success">
        Payment received. Your celestial paperwork is ready below.
    </div>

    <?php if (!empty($downloadFiles)): ?>
        <p>Your downloads should start automatically. If not, use the links:</p>
        <ul class="mb-4">
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
        <p>No downloadable certificates were found for this order.</p>
    <?php endif; ?>

    <a href="index.php" class="btn btn-dark btn-sm mt-2">Back to shop</a>

<?php else: ?>
    <div class="alert alert-warning">Payment status could not be confirmed.</div>
    <a href="cart.php" class="btn btn-secondary btn-sm">Back to cart</a>
<?php endif; ?>

<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Order complete';
$activePage = 'shop';
include __DIR__ . '/layout.php';
