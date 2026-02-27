<?php
session_start();

$products = json_decode(file_get_contents(__DIR__ . '/../data/products.json'), true);
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add-to-cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = (int) $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? max(1, (int) $_POST['quantity']) : 1;

    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = 0;
    }
    $_SESSION['cart'][$productId] += $quantity;

    header('Location: cart.php');
    exit;
}

$cartCount = array_sum($_SESSION['cart']);

// Build page-specific HTML content into buffer
ob_start();
?>
<div class="shop-header">
    <div>
        <h1>Shop the Halo</h1>
        <p>Charms, kits, and readings for mortals who like a little magic with their coffee.</p>
    </div>
    <a href="cart.php" class="btn btn-outline-dark btn-sm shop-cart-button">
        <i class="ph ph-shopping-cart"></i>
        Cart (<?php echo $cartCount; ?>)
    </a>
</div>

<div class="shop-grid">
    <?php foreach ($products as $product): ?>
        <div class="shop-card">
            <div class="shop-card-header">
                <span class="shop-card-pill">featured</span>
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            </div>
            <p class="shop-card-desc">
                A little celestial nudge in mortal form. Perfect for Hex &amp; Halo disciples.
            </p>
            <div class="shop-card-meta">
                <div class="shop-card-price">€ <?php echo number_format($product['price'], 2); ?></div>
                <form method="post" class="shop-card-form">
                    <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                    <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm">
                    <button type="submit" class="btn btn-dark btn-sm">
                        add to halo
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Hex & Halo – Shop';

include __DIR__ . '/layout.php';
