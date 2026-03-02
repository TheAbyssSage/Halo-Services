<?php
// public/blessing.php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $startDate = $_POST['start_date'] ?: date('Y-m-d');

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        $endDate = date('Y-m-d', strtotime($startDate . ' +7 days'));

        // 1) Generate QR
        $qr = new QrCode('Weekly Blessing – ' . $name . ' – ' . $startDate . ' to ' . $endDate);
        $writer = new PngWriter();
        $result = $writer->write($qr);
        $qrBase64 = base64_encode($result->getString());
        $qrImgTag = '<img src="data:image/png;base64,' . $qrBase64 . '" width="120" alt="Blessing QR">';

        // 2) Build PDF HTML
        ob_start();
?>
        <h1>Certificate of Temporary Divine Favor</h1>
        <p>
            This certifies that <strong><?php echo htmlspecialchars($name); ?></strong>
            is officially blessed from
            <strong><?php echo htmlspecialchars($startDate); ?></strong>
            until
            <strong><?php echo htmlspecialchars($endDate); ?></strong>.
        </p>
        <p>
            Side effects may include: better coffee, minor plot armor, and fewer cursed encounters.
        </p>
        <p>Issued by Hex &amp; Halo, Bureau of Gentle Miracles.</p>
        <p><?php echo $qrImgTag; ?></p>
<?php
        $html = ob_get_clean();

        // 3) Render PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        // 4) Save to temporary storage
        $tmpDir = __DIR__ . '/tmp_certs';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }
        $fileName = 'blessing-' . preg_replace('/\W+/', '-', strtolower($name)) . '-' . time() . '.pdf';
        $pdfPath  = $tmpDir . '/' . $fileName;
        file_put_contents($pdfPath, $pdfOutput);

        // 5) Add as item to cart
        $_SESSION['cart'][] = [
            'id'          => 'cert_blessing_' . time(),
            'type'        => 'certificate',
            'certificate' => 'blessing',
            'display'     => 'Weekly Blessing for ' . $name,
            'price'       => 4.99, // set your price
            'file_path'   => 'tmp_certs/' . $fileName, // relative to public/
            'meta'        => [
                'name'       => $name,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ],
        ];

        // 6) Redirect to cart
        header('Location: cart.php');
        exit;
    }
}

// Render page in layout
ob_start();
?>
<h1>Weekly Blessing Infusion</h1>
<p class="mb-3">
    Generate a blessing-infused certificate that wards off minor evil for one mortal week.
    It will be added to your cart as a celestial product.
</p>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post" action="blessing.php" class="mb-4">
    <div class="mb-3">
        <label class="form-label">Your name</label>
        <input type="text" name="name" class="form-control"
            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Start date</label>
        <input type="date" name="start_date" class="form-control"
            value="<?php echo htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')); ?>">
    </div>

    <button type="submit" class="btn btn-dark btn-sm">Prepare blessing</button>
</form>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Weekly Blessing';
$activePage = 'certificate';

include __DIR__ . '/layout.php';
