<?php
// public/protection.php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$error = null;

// Tier config
$tiers = [
    'light'      => ['label' => 'Light Ward',           'price' => 3.99,  'desc' => 'gentle nudge against bad vibes'],
    'standard'   => ['label' => 'Standard Ward',        'price' => 6.99,  'desc' => 'everyday angelic coverage'],
    'archangel'  => ['label' => 'Archangel Priority',   'price' => 12.99, 'desc' => 'for boss fights and exam weeks'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $tier      = $_POST['tier'] ?? 'standard';
    $startDate = $_POST['start_date'] ?: date('Y-m-d');

    if (!array_key_exists($tier, $tiers)) $tier = 'standard';

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        $tierInfo  = $tiers[$tier];
        $endDate   = date('Y-m-d', strtotime($startDate . ' +30 days'));

        // 1) Generate QR
        $qrText = 'Angelic Protection – ' . $tierInfo['label'] . ' – ' . $name . ' – ' . $startDate . ' to ' . $endDate;
        $qr     = new QrCode($qrText);
        $writer = new PngWriter();
        $result = $writer->write($qr);
        $qrBase64  = base64_encode($result->getString());
        $qrImgTag  = '<img src="data:image/png;base64,' . $qrBase64 . '" width="120" alt="Protection QR">';

        // 2) Build PDF HTML
        ob_start();
?>
        <h1>Angelic Protection Seal</h1>
        <h2><?php echo htmlspecialchars($tierInfo['label']); ?></h2>
        <p>
            This certifies that <strong><?php echo htmlspecialchars($name); ?></strong>
            is under official angelic protection
            from <strong><?php echo htmlspecialchars($startDate); ?></strong>
            to <strong><?php echo htmlspecialchars($endDate); ?></strong>.
        </p>
        <p>
            Protection tier: <em><?php echo htmlspecialchars($tierInfo['desc']); ?></em>.
            Angels are on standby. Please do not provoke anything with feathers.
        </p>
        <p>Issued by Hex &amp; Halo, Bureau of Celestial Affairs.</p>
        <p><?php echo $qrImgTag; ?></p>
<?php
        $html = ob_get_clean();

        // 3) Render PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        // 4) Save
        $tmpDir = __DIR__ . '/tmp_certs';
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);
        $fileName = 'protection-' . preg_replace('/\W+/', '-', strtolower($name)) . '-' . time() . '.pdf';
        $pdfPath  = $tmpDir . '/' . $fileName;
        file_put_contents($pdfPath, $pdfOutput);

        // 5) Add to cart
        $_SESSION['cart'][] = [
            'id'          => 'cert_protection_' . time(),
            'type'        => 'certificate',
            'certificate' => 'protection',
            'display'     => $tierInfo['label'] . ' for ' . $name,
            'price'       => $tierInfo['price'],
            'file_path'   => 'tmp_certs/' . $fileName,
            'meta'        => [
                'name'       => $name,
                'tier'       => $tier,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ],
        ];

        header('Location: cart.php');
        exit;
    }
}

ob_start();
?>
<h1>Angelic Protection Seal</h1>
<p class="mb-3">
    One-month angelic protection, with tiers from light ward to archangel priority.
</p>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post" action="protection.php" class="mb-4">
    <div class="mb-3">
        <label class="form-label">Your name</label>
        <input type="text" name="name" class="form-control"
            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Protection tier</label>
        <select name="tier" class="form-select">
            <option value="light" <?php echo (($_POST['tier'] ?? '') === 'light')     ? 'selected' : ''; ?>>
                Light Ward – €3.99 – gentle nudge against bad vibes
            </option>
            <option value="standard" <?php echo (($_POST['tier'] ?? 'standard') === 'standard') ? 'selected' : ''; ?>>
                Standard Ward – €6.99 – everyday angelic coverage
            </option>
            <option value="archangel" <?php echo (($_POST['tier'] ?? '') === 'archangel') ? 'selected' : ''; ?>>
                Archangel Priority – €12.99 – for boss fights and exam weeks
            </option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Start date</label>
        <input type="date" name="start_date" class="form-control"
            value="<?php echo htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')); ?>">
    </div>

    <button type="submit" class="btn btn-dark btn-sm">Add to cart</button>
</form>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Angelic Protection';
$activePage = 'certificate';
include __DIR__ . '/layout.php';
