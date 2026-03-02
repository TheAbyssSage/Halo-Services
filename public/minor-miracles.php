<?php
// public/minor-miracles.php
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
    'tiny'     => ['label' => 'Tiny Wonders',    'price' => 1.99, 'count' => 3],
    'standard' => ['label' => 'Standard Wonders', 'price' => 4.99, 'count' => 7],
    'extra'    => ['label' => 'Extra Sparkly',    'price' => 8.99, 'count' => 13],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $tier = $_POST['tier'] ?? 'standard';
    if (!array_key_exists($tier, $tiers)) $tier = 'standard';

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        $tierInfo  = $tiers[$tier];
        $issueDate = date('Y-m-d');
        $expiry    = date('Y-m-d', strtotime('+90 days'));

        // 1) QR
        $qrText   = 'Minor Miracles Pass – ' . $tierInfo['label'] . ' – ' . $name . ' – ' . $tierInfo['count'] . ' miracles – expires ' . $expiry;
        $qr       = new QrCode($qrText);
        $writer   = new PngWriter();
        $result   = $writer->write($qr);
        $qrBase64 = base64_encode($result->getString());
        $qrImgTag = '<img src="data:image/png;base64,' . $qrBase64 . '" width="120" alt="Miracles QR">';

        // 2) PDF
        ob_start();
?>
        <h1>Minor Miracles Pass</h1>
        <h2><?php echo htmlspecialchars($tierInfo['label']); ?></h2>
        <p>
            This pass entitles <strong><?php echo htmlspecialchars($name); ?></strong>
            to <strong><?php echo $tierInfo['count']; ?> minor miracles</strong>,
            redeemable at any point before <strong><?php echo $expiry; ?></strong>.
        </p>
        <p>
            Miracles are non-transferable, subject to cosmic availability,
            and cannot be exchanged for cash, glory, or parking spots in busy areas.
            Issued on <?php echo $issueDate; ?>.
        </p>
        <p>Issued by Hex &amp; Halo, Office of Tiny Wonders.</p>
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
        $fileName = 'miracles-' . preg_replace('/\W+/', '-', strtolower($name)) . '-' . time() . '.pdf';
        file_put_contents($tmpDir . '/' . $fileName, $pdfOutput);

        // 5) Cart
        $_SESSION['cart'][] = [
            'id'          => 'cert_miracles_' . time(),
            'type'        => 'certificate',
            'certificate' => 'minor-miracles',
            'display'     => $tierInfo['label'] . ' Pass for ' . $name,
            'price'       => $tierInfo['price'],
            'file_path'   => 'tmp_certs/' . $fileName,
            'meta'        => [
                'name'   => $name,
                'tier'   => $tier,
                'count'  => $tierInfo['count'],
                'expiry' => $expiry,
            ],
        ];

        header('Location: cart.php');
        exit;
    }
}

ob_start();
?>
<h1>Minor Miracles Pass</h1>
<p class="mb-3">
    Claim an official pass that entitles you to a reasonable number of minor miracles.
</p>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post" action="minor-miracles.php" class="mb-4">
    <div class="mb-3">
        <label class="form-label">Your name</label>
        <input type="text" name="name" class="form-control"
            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Miracle tier</label>
        <select name="tier" class="form-select">
            <option value="tiny" <?php echo (($_POST['tier'] ?? '') === 'tiny')     ? 'selected' : ''; ?>>
                Tiny Wonders – €1.99 – 3 minor miracles
            </option>
            <option value="standard" <?php echo (($_POST['tier'] ?? 'standard') === 'standard') ? 'selected' : ''; ?>>
                Standard Wonders – €4.99 – 7 minor miracles
            </option>
            <option value="extra" <?php echo (($_POST['tier'] ?? '') === 'extra')    ? 'selected' : ''; ?>>
                Extra Sparkly – €8.99 – 13 minor miracles
            </option>
        </select>
    </div>

    <button type="submit" class="btn btn-dark btn-sm">Add to cart</button>
</form>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Minor Miracles';
$activePage = 'certificate';
include __DIR__ . '/layout.php';
