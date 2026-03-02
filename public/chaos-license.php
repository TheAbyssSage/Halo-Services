<?php
// public/chaos-license.php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$error = null;

$tiers = [
    'apprentice' => ['label' => 'Apprentice Gremlin',    'price' => 2.99,  'desc' => 'supervised mischief only'],
    'licensed'   => ['label' => 'Licensed Chaos Gremlin', 'price' => 5.99,  'desc' => 'standard field license'],
    'overseer'   => ['label' => 'Chaos Overseer',        'price' => 9.99,  'desc' => 'trusted with serious nonsense'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $tier = $_POST['tier'] ?? 'licensed';
    if (!array_key_exists($tier, $tiers)) $tier = 'licensed';

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        $tierInfo  = $tiers[$tier];
        $issueDate = date('Y-m-d');
        $expiry    = date('Y-m-d', strtotime('+1 year'));

        // 1) QR code
        $qrText   = 'Chaos License – ' . $tierInfo['label'] . ' – ' . $name . ' – expires ' . $expiry;
        $qr       = new QrCode($qrText);
        $writer   = new PngWriter();
        $result   = $writer->write($qr);
        $qrBase64 = base64_encode($result->getString());
        $qrImgTag = '<img src="data:image/png;base64,' . $qrBase64 . '" width="120" alt="License QR">';

        // 2) PDF HTML
        ob_start();
?>
        <h1>Chaos Containment License</h1>
        <h2><?php echo htmlspecialchars($tierInfo['label']); ?></h2>
        <p>
            This license is issued to <strong><?php echo htmlspecialchars($name); ?></strong>
            and authorises the holder to engage in <?php echo htmlspecialchars($tierInfo['desc']); ?>,
            effective <strong><?php echo $issueDate; ?></strong>
            and valid until <strong><?php echo $expiry; ?></strong>.
        </p>
        <p>
            The Halo Bureau assumes no responsibility for property damage, confused bystanders,
            or spontaneous glitter. License void if used during full moon without notarised permission slip.
        </p>
        <p>Issued by Hex &amp; Halo, Department of Contained Nonsense.</p>
        <p><?php echo $qrImgTag; ?></p>
<?php
        $html = ob_get_clean();

        // 3) Render
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        // 4) Save
        $tmpDir = __DIR__ . '/tmp_certs';
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);
        $fileName = 'chaos-' . preg_replace('/\W+/', '-', strtolower($name)) . '-' . time() . '.pdf';
        file_put_contents($tmpDir . '/' . $fileName, $pdfOutput);

        // 5) Cart
        $_SESSION['cart'][] = [
            'id'          => 'cert_chaos_' . time(),
            'type'        => 'certificate',
            'certificate' => 'chaos-license',
            'display'     => $tierInfo['label'] . ' for ' . $name,
            'price'       => $tierInfo['price'],
            'file_path'   => 'tmp_certs/' . $fileName,
            'meta'        => [
                'name'   => $name,
                'tier'   => $tier,
                'expiry' => $expiry,
            ],
        ];

        header('Location: cart.php');
        exit;
    }
}

ob_start();
?>
<h1>Chaos Containment License</h1>
<p class="mb-3">
    Get officially licensed to wield small, contained chaos under Hex &amp; Halo supervision.
</p>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post" action="chaos-license.php" class="mb-4">
    <div class="mb-3">
        <label class="form-label">Your name</label>
        <input type="text" name="name" class="form-control"
            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">License tier</label>
        <select name="tier" class="form-select">
            <option value="apprentice" <?php echo (($_POST['tier'] ?? '') === 'apprentice') ? 'selected' : ''; ?>>
                Apprentice Gremlin – €2.99 – supervised mischief only
            </option>
            <option value="licensed" <?php echo (($_POST['tier'] ?? 'licensed') === 'licensed') ? 'selected' : ''; ?>>
                Licensed Chaos Gremlin – €5.99 – standard field license
            </option>
            <option value="overseer" <?php echo (($_POST['tier'] ?? '') === 'overseer') ? 'selected' : ''; ?>>
                Chaos Overseer – €9.99 – trusted with serious nonsense
            </option>
        </select>
    </div>

    <button type="submit" class="btn btn-dark btn-sm">Add to cart</button>
</form>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Chaos License';
$activePage = 'certificate';
include __DIR__ . '/layout.php';
