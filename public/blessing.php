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
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <title>Certificate of Temporary Divine Favor</title>
            <style>
                body {
                    margin: 0;
                    padding: 40px;
                    font-family: 'Inter', sans-serif;
                    background-color: #EAEBE8;
                    color: #3C3E4A;
                }

                .card {
                    background-color: #F7F7F4;
                    border-radius: 14px;
                    border: 1px solid #D1D4CD;
                    padding: 32px 36px;
                    box-sizing: border-box;
                    box-shadow: 0 0 0 12px #EAEBE8;
                    position: relative;
                    overflow: hidden;
                }

                .card::before {
                    content: "";
                    position: absolute;
                    inset: -60px;
                    background: radial-gradient(circle at top right, rgba(214, 183, 108, 0.25), transparent 60%);
                    opacity: 0.9;
                    pointer-events: none;
                }

                h1 {
                    margin: 0 0 10px 0;
                    font-family: 'DM Sans', sans-serif;
                    font-weight: 900;
                    font-size: 26px;
                    letter-spacing: 2px;
                    text-transform: uppercase;
                    position: relative;
                    z-index: 1;
                }

                h1 span {
                    display: block;
                    font-size: 44px;
                    letter-spacing: -2px;
                    background: linear-gradient(to bottom, #D6B76C, #3C3E4A 75%);
                    -webkit-background-clip: text;
                    background-clip: text;
                    color: transparent;
                }

                .subtitle {
                    font-size: 11px;
                    text-transform: uppercase;
                    letter-spacing: 3px;
                    margin-bottom: 18px;
                    position: relative;
                    z-index: 1;
                }

                .pill {
                    display: inline-block;
                    border-radius: 999px;
                    border: 1px solid #D1D4CD;
                    background-color: #EAEBE8;
                    padding: 4px 10px;
                    font-size: 9px;
                    text-transform: uppercase;
                    letter-spacing: 1.5px;
                    margin-bottom: 10px;
                    position: relative;
                    z-index: 1;
                }

                p,
                ul {
                    position: relative;
                    z-index: 1;
                }

                p {
                    font-size: 12px;
                    line-height: 1.7;
                    margin: 0 0 10px 0;
                }

                ul {
                    margin: 8px 0 12px 16px;
                    padding: 0;
                    font-size: 11px;
                    line-height: 1.6;
                }

                li {
                    margin-bottom: 4px;
                }

                .emphasis {
                    font-style: italic;
                    color: #555;
                }

                .footer {
                    margin-top: 16px;
                    font-size: 10px;
                    color: #777;
                }

                .seraph-note {
                    font-size: 10px;
                    color: #555;
                    margin-top: 6px;
                }

                strong {
                    font-weight: 700;
                }
            </style>
        </head>

        <body>
            <div class="card">
                <div class="pill">Hex &amp; Halo // Bureau of Gentle Miracles</div>

                <h1>
                    Certificate of
                    <span>Divine Favor</span>
                </h1>

                <p class="subtitle">
                    WEEKLY BLESSING INFUSION – OBSERVED BY MANY‑EYED SERAPHS
                </p>

                <p>
                    Let it be recorded that
                    <strong><?php echo htmlspecialchars($name); ?></strong>
                    is placed under temporary, luminous favor from
                    <strong><?php echo htmlspecialchars($startDate); ?></strong>
                    until
                    <strong><?php echo htmlspecialchars($endDate); ?></strong>.
                </p>

                <p>
                    For the duration of this week, a small assembly of biblically accurate angels
                    (wheels within wheels, wings upon wings, and eyes in every direction)
                    has been instructed to gently tilt probability in the bearer’s favor.
                </p>

                <p>
                    Expected manifestations may include:
                </p>

                <ul>
                    <li>Moments of uncanny timing where doors open just as you arrive.</li>
                    <li>Chance encounters that feel scripted by something with too many wings.</li>
                    <li>A noticeable reduction in cursed events, glitches, and narrative dead-ends.</li>
                </ul>

                <p class="emphasis">
                    Witnesses may report the distant rustle of wings, soft golden static,
                    or the feeling of being calmly watched by something vast and benevolent.
                </p>

                <p>
                    This favor does not guarantee invincibility; it simply ensures that
                    when chaos rolls its dice, a quiet choir of eyes and halos leans
                    ever so slightly on the table.
                </p>

                <p class="footer">
                    Issued by Hex &amp; Halo, Bureau of Gentle Miracles.
                    Valid for one mortal week only. Renewal requires additional petitions and at least one snack
                    offering left somewhere aesthetically pleasing.
                </p>
            </div>
        </body>

        </html>
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
