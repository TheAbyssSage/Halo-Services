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
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <title>Minor Miracles Pass</title>
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
                    background: radial-gradient(circle at top left, rgba(214, 183, 108, 0.22), transparent 60%);
                    opacity: 1;
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

                .seraphic {
                    font-style: italic;
                    color: #555;
                    font-size: 11px;
                }
            </style>
        </head>

        <body>
            <div class="card">
                <div class="pill">Hex &amp; Halo // Office of Tiny Wonders</div>

                <h1>Minor Miracles Pass</h1>
                <h2><?php echo htmlspecialchars($tierInfo['label']); ?></h2>

                <p>
                    This pass attests that
                    <strong><?php echo htmlspecialchars($name); ?></strong>
                    has been granted
                    <strong><?php echo $tierInfo['count']; ?> minor miracles</strong>,
                    redeemable at any point before
                    <strong><?php echo $expiry; ?></strong>.
                </p>

                <p class="meta">
                    Issued on <strong><?php echo $issueDate; ?></strong> in the presence of low‑ranking cherubim,
                    whose many eyes will quietly witness each miracle expended.
                </p>

                <?php if ($tier === 'tiny'): ?>
                    <p>
                        The <strong>Tiny Wonders</strong> tier focuses on soft ripples in the timeline:
                    </p>
                    <ul>
                        <li>Coin flips landing just right when the stakes are small but your heart cares.</li>
                        <li>Last‑minute discoveries of keys, cards, and headphones you swore were gone.</li>
                        <li>
                            Background music aligning perfectly with your mood as if chosen
                            by a many‑eyed playlist curator in the sky.
                        </li>
                    </ul>
                    <p class="seraphic">
                        These miracles are overseen by quietly humming orbs of light who approve of
                        harmless, wholesome convenience.
                    </p>
                <?php elseif ($tier === 'standard'): ?>
                    <p>
                        The <strong>Standard Wonders</strong> tier grants a practical bundle of reality edits:
                    </p>
                    <ul>
                        <li>Conversation threads that refuse to become arguments despite every opportunity.</li>
                        <li>Exam questions nudging toward the topics you actually reviewed.</li>
                        <li>
                            Plans falling apart just in time to reveal a better, stranger option
                            that somehow fits perfectly.
                        </li>
                    </ul>
                    <p class="seraphic">
                        Minor seraphim will observe, wings folded and eyes open, ensuring the chaos
                        bends but does not break around you.
                    </p>
                <?php elseif ($tier === 'extra'): ?>
                    <p>
                        The <strong>Extra Sparkly</strong> tier is reserved for those whose lives
                        demand cinematic timing:
                    </p>
                    <ul>
                        <li>
                            Coincidences so sharp they feel like the script was rewritten
                            five minutes before the scene.
                        </li>
                        <li>Lighting, weather, and ambience conspiring to give you main‑character moments.</li>
                        <li>
                            Encounters, opportunities, and messages that arrive at the exact second
                            they are most needed.
                        </li>
                    </ul>

                    <p class="seraphic">
                        This pass is monitored by higher‑order, biblically accurate attendants:
                        rings of flame and halos dense with eyes, all very invested
                        in your personal subplot.
                    </p>

                    <div class="qr-wrap">
                        <div class="qr-note">
                            Extra Sparkly class only – scan to confirm miracle ledger in the Hex &amp; Halo registry.
                        </div>
                        <?php echo $qrImgTag; ?>
                    </div>
                <?php endif; ?>

                <p class="footer">
                    Miracles are non-transferable, subject to cosmic availability, and may not be redeemed
                    for cash, glory, or guaranteed parking. Overuse may attract curious angels, eldritch but kind.
                </p>
            </div>
        </body>

        </html>
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
