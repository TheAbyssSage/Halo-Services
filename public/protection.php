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
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <title>Angelic Protection Seal</title>
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
                    inset: -80px;
                    background: radial-gradient(circle at bottom right, rgba(60, 62, 74, 0.22), transparent 60%);
                    opacity: 0.9;
                    pointer-events: none;
                }

                h1 {
                    margin: 0 0 6px 0;
                    font-family: 'DM Sans', sans-serif;
                    font-weight: 800;
                    font-size: 24px;
                    letter-spacing: 2px;
                    text-transform: uppercase;
                    position: relative;
                    z-index: 1;
                }

                h2 {
                    margin: 0 0 16px 0;
                    font-family: 'DM Sans', sans-serif;
                    font-weight: 700;
                    font-size: 18px;
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
                ul,
                .meta,
                .footer,
                .qr-wrap {
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

                .meta {
                    font-size: 11px;
                    color: #666;
                    margin-top: 6px;
                }

                .footer {
                    margin-top: 14px;
                    font-size: 10px;
                    color: #777;
                }

                .qr-wrap {
                    margin-top: 14px;
                    text-align: right;
                }

                .qr-note {
                    font-size: 9px;
                    color: #666;
                    margin-bottom: 4px;
                }

                .eldritch {
                    font-style: italic;
                    color: #555;
                    font-size: 11px;
                }
            </style>
        </head>

        <body>
            <div class="card">
                <div class="pill">Hex &amp; Halo // Bureau of Celestial Affairs</div>

                <h1>Angelic Protection Seal</h1>
                <h2><?php echo htmlspecialchars($tierInfo['label']); ?></h2>

                <p>
                    This document certifies that
                    <strong><?php echo htmlspecialchars($name); ?></strong>
                    is placed under sanctioned angelic protection from
                    <strong><?php echo htmlspecialchars($startDate); ?></strong>
                    to
                    <strong><?php echo htmlspecialchars($endDate); ?></strong>.
                </p>

                <p class="meta">
                    Protection tier: <em><?php echo htmlspecialchars($tierInfo['desc']); ?></em>.
                </p>

                <?php if ($tier === 'light'): ?>
                    <p>
                        Under the <strong>Light Ward</strong>, a small ring of gentle, many‑eyed guardians
                        keeps watch at a respectful distance:
                    </p>
                    <ul>
                        <li>Deflecting stray bad vibes and background malice before they fully form.</li>
                        <li>Smoothing sharp edges off everyday stressors and social static.</li>
                        <li>Quietly nudging you away from the most cursed possible decisions.</li>
                    </ul>
                    <p class="eldritch">
                        These entities are technically terrifying, but have been briefed on “being chill”
                        in mortal spaces.
                    </p>
                <?php elseif ($tier === 'standard'): ?>
                    <p>
                        Under the <strong>Standard Ward</strong>, a small choir of biblically accurate angels
                        rotates around your story:
                    </p>
                    <ul>
                        <li>
                            Wheels within wheels interceding at key moments:
                            last‑second saves, softened blows, near‑miss accidents.
                        </li>
                        <li>Negative encounters dampened or diverted into oddly harmless outcomes.</li>
                        <li>Important choices lit up from strange angles, making your best path clearer.</li>
                    </ul>
                    <p class="eldritch">
                        Their forms may be glimpsed in reflections, peripheral vision, or oddly timed flickers
                        of light. This is normal. Probably.
                    </p>
                <?php elseif ($tier === 'archangel'): ?>
                    <p>
                        Under the <strong>Archangel Priority</strong> tier, high‑ranking, lore‑accurate angels
                        are assigned to your case file:
                    </p>
                    <ul>
                        <li>
                            Immediate escalation of critical threats to beings composed of wings, eyes,
                            and very loud hymns.
                        </li>
                        <li>
                            Boss fights, exam weeks, and turning‑point conversations granted
                            additional narrative armor.
                        </li>
                        <li>
                            Surge protection against despair, doom spirals, and self‑sabotage,
                            enforced by entities older than language.
                        </li>
                    </ul>

                    <p class="eldritch">
                        Reported side effects include vivid dreams of orbiting halos, sudden calm
                        in the middle of chaos, and the unshakable feeling that something enormous
                        and kind just nodded.
                    </p>

                    <div class="qr-wrap">
                        <div class="qr-note">
                            Archangel tier – scan to access celestial dispatch log for this assignment.
                        </div>
                        <?php echo $qrImgTag; ?>
                    </div>
                <?php endif; ?>

                <p class="footer">
                    Angels are on standby. Do not deliberately provoke entities described in ancient texts
                    as “do not be afraid” unless absolutely necessary. Hex &amp; Halo accepts no liability
                    for voluntary recklessness or unsanctioned demon negotiations.
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
