<?php
session_start();


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle removing items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Remove normal products by product ID (old style)
    if (isset($_POST['remove_id'])) {
        $removeId = (int) $_POST['remove_id'];
        if (isset($_SESSION['cart'][$removeId])) {
            unset($_SESSION['cart'][$removeId]);
        }
    }

    // Remove certificate items by index (new style)
    if (isset($_POST['remove_index'])) {
        $idx = (int) $_POST['remove_index'];
        if (isset($_SESSION['cart'][$idx]) && is_array($_SESSION['cart'][$idx]) && ($_SESSION['cart'][$idx]['type'] ?? '') === 'certificate') {
            unset($_SESSION['cart'][$idx]);
            // Reindex to keep indices clean
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }

    header('Location: cart.php');
    exit;
}

$cart  = $_SESSION['cart'];
$total = 0;

// Build content for layout
ob_start();
?>
<h1>Your Halo Cart</h1>
<p class="mb-3">
    Charms, rituals, and official paperwork from the Hex &amp; Halo bureau.
</p>

<?php if (empty($cart)): ?>
    <p>Your cart is empty. The angels are patient.</p>
    <a href="index.php" class="btn btn-dark btn-sm">Back to shop</a>
<?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $key => $item): ?>
                    <?php
                    // Certificate items: full objects with 'type' = 'certificate'
                    if (is_array($item) && ($item['type'] ?? '') === 'certificate') {
                        $name  = $item['display'] ?? 'Certificate';
                        $price = (float) ($item['price'] ?? 0);
                        $qty   = 1;
                        $subtotal = $price * $qty;
                        $total   += $subtotal;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($name); ?></td>
                            <td class="text-center">1</td>
                            <td class="text-end">€ <?php echo number_format($price, 2); ?></td>
                            <td class="text-end">€ <?php echo number_format($subtotal, 2); ?></td>
                            <td class="text-end">
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="remove_index" value="<?php echo (int) $key; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php
                        continue;
                    }

                    // Normal products: key = productId, value = quantity
                    $productId = (int) $key;
                    $qty       = (int) $item;

                    if (!isset($productsById[$productId])) {
                        continue;
                    }
                    $product  = $productsById[$productId];
                    $subtotal = $product['price'] * $qty;
                    $total   += $subtotal;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td class="text-center"><?php echo $qty; ?></td>
                        <td class="text-end">€ <?php echo number_format($product['price'], 2); ?></td>
                        <td class="text-end">€ <?php echo number_format($subtotal, 2); ?></td>
                        <td class="text-end">
                            <form method="post" class="d-inline">
                                <input type="hidden" name="remove_id" value="<?php echo (int) $productId; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <strong>Total: € <?php echo number_format($total, 2); ?></strong>
        <a href="checkout.php" class="btn btn-dark btn-sm">Proceed to checkout</a>
    </div>
<?php endif; ?>

<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Cart';
$activePage = 'cart';

include __DIR__ . '/layout.php';
