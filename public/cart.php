<?php
// public/cart.php
session_start();

$products = json_decode(file_get_contents(__DIR__ . '/../data/products.json'), true);
$productsById = [];
foreach ($products as $p) {
    $productsById[$p['id']] = $p;
}

$cart = $_SESSION['cart'] ?? [];

// Handle removing items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $removeId = (int) $_POST['remove_id'];
    unset($cart[$removeId]);
    $_SESSION['cart'] = $cart;
}

$total = 0;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">
    <div class="container">
        <h1 class="mb-4">Your Cart</h1>

        <p><a href="index.php" class="btn btn-secondary">Back to products</a></p>

        <?php if (empty($cart)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price each</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $productId => $qty): ?>
                        <?php
                        if (!isset($productsById[$productId])) {
                            continue;
                        }
                        $product = $productsById[$productId];
                        $subtotal = $product['price'] * $qty;
                        $total += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo $qty; ?></td>
                            <td>€ <?php echo number_format($product['price'], 2); ?></td>
                            <td>€ <?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="remove_id" value="<?php echo $productId; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Total: € <?php echo number_format($total, 2); ?></h3>

            <a href="checkout.php" class="btn btn-primary">Proceed to checkout</a>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>