<?php
// public/checkout.php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

$productsFile = __DIR__ . '/../data/products.json';
$ordersFile   = __DIR__ . '/../data/orders.json';

$products = json_decode(file_get_contents($productsFile), true);
$productsById = [];
foreach ($products as $p) {
    $productsById[$p['id']] = $p;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

$errors = [];
$orderCreated = false;
$orderId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    if (empty($errors)) {
        // Build items and total
        $items = [];
        $total = 0;
        foreach ($cart as $productId => $qty) {
            if (!isset($productsById[$productId])) {
                continue;
            }
            $product  = $productsById[$productId];
            $subtotal = $product['price'] * $qty;
            $total   += $subtotal;

            $items[] = [
                'product_id' => $productId,
                'name'       => $product['name'],
                'quantity'   => $qty,
                'price'      => $product['price'],
                'subtotal'   => $subtotal,
            ];
        }

        // Load existing orders
        $orders = [];
        if (file_exists($ordersFile)) {
            $decoded = json_decode(file_get_contents($ordersFile), true);
            if (is_array($decoded)) {
                $orders = $decoded;
            }
        }

        // Create new order
        $orderId = count($orders) + 1;
        $order = [
            'id'         => $orderId,
            'name'       => $name,
            'email'      => $email,
            'items'      => $items,
            'total'      => $total,
            'created_at' => date('c'),
        ];
        $orders[] = $order;

        file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT));

        // === QR code (your working pattern, adapted) ===
        // You can put any string here; for now use the order ID
        $qr = new QrCode('Order #' . $orderId);
        $writer = new PngWriter();
        $result = $writer->write($qr);

        // Base64-encode the PNG so we can embed it directly in HTML/PDF
        $qrBase64 = base64_encode($result->getString());
        $qrImgTag = '<img src="data:image/png;base64,' . $qrBase64 . '" width="150" alt="Order QR Code">';

        // Generate PDF invoice using Dompdf
        $html = '<h1>Invoice for Order #' . $orderId . '</h1>';
        $html .= '<p>Name: ' . htmlspecialchars($name) . '</p>';
        $html .= '<p>Email: ' . htmlspecialchars($email) . '</p>';
        $html .= '<table border="1" cellspacing="0" cellpadding="4">
                    <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>';
        foreach ($items as $item) {
            $html .= '<tr>
                        <td>' . htmlspecialchars($item['name']) . '</td>
                        <td>' . (int) $item['quantity'] . '</td>
                        <td>' . number_format($item['price'], 2) . '</td>
                        <td>' . number_format($item['subtotal'], 2) . '</td>
                      </tr>';
        }
        $html .= '</table>';
        $html .= '<h3>Total: € ' . number_format($total, 2) . '</h3>';
        $html .= '<p>Scan this QR code for your order reference:</p>';
        $html .= $qrImgTag;

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        $pdfPath = __DIR__ . '/../data/order-' . $orderId . '-invoice.pdf';
        file_put_contents($pdfPath, $pdfOutput);

        // Send confirmation email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Configure SMTP if needed; for now use mail() transport
            // $mail->isSMTP();
            // $mail->Host = 'smtp.example.com';
            // ...

            $mail->setFrom('no-reply@example.com', 'Student Webshop');
            $mail->addAddress($email, $name);
            $mail->Subject = 'Your order #' . $orderId;
            $mail->Body = "Thank you for your order!\n\nOrder ID: {$orderId}\nTotal: € " . number_format($total, 2);
            $mail->addAttachment($pdfPath, 'invoice.pdf');

            $mail->isMail();
            $mail->send();
        } catch (MailException $e) {
            // error_log($e->getMessage());
        }

        // Clear cart after successful order
        $_SESSION['cart'] = [];

        $orderCreated = true;
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">
    <div class="container">
        <h1 class="mb-4">Checkout</h1>

        <p><a href="cart.php" class="btn btn-secondary">Back to cart</a></p>

        <?php if ($orderCreated): ?>
            <div class="alert alert-success">
                Your order #<?php echo (int) $orderId; ?> has been placed. A confirmation email was sent (if mail is configured).
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <h3>Order summary</h3>
            <ul>
                <?php
                $total = 0;
                foreach ($cart as $productId => $qty):
                    if (!isset($productsById[$productId])) {
                        continue;
                    }
                    $product  = $productsById[$productId];
                    $subtotal = $product['price'] * $qty;
                    $total   += $subtotal;
                ?>
                    <li>
                        <?php echo htmlspecialchars($product['name']); ?>
                        (x<?php echo (int) $qty; ?>)
                        — € <?php echo number_format($subtotal, 2); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Total: € <?php echo number_format($total, 2); ?></strong></p>

            <form method="post" class="mt-4">
                <div class="mb-3">
                    <label class="form-label">Name*</label>
                    <input type="text" name="name" class="form-control"
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email*</label>
                    <input type="email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn btn-success">Place order</button>
            </form>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>