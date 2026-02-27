<?php
// public/index.php
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
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Student Webshop - Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">
    <div class="container">
        <h1 class="mb-4">Products</h1>

        <p><a href="cart.php" class="btn btn-primary">View Cart (<?php echo array_sum($_SESSION['cart']); ?>)</a></p>

        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-3">
                    <div class="card p-3">
                        <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p>€ <?php echo number_format($product['price'], 2); ?></p>
                        <form method="post">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div class="input-group">
                                <input type="number" name="quantity" value="1" min="1" class="form-control">
                                <button type="submit" class="btn btn-success">Add to cart</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>