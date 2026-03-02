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
                    padding: 48px;
                    font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
                    background-color: #181922;
                    background-image:
                        radial-gradient(circle at top left, #3C3E4A 0, #181922 55%),
                        radial-gradient(circle at bottom right, #20222E 0, #181922 60%);
                    color: #F7F7F4;
                }

                .document {
                    position: relative;
                    box-sizing: border-box;
                    background: #F7F7F4;
                    color: #2F3140;
                    border-radius: 18px;
                    border: 1px solid #D1D4CD;
                    padding: 40px 44px 34px 44px;
                    box-shadow:
                        0 0 0 6px #EAEBE8,
                        0 30px 80px rgba(0, 0, 0, 0.6);
                    overflow: hidden;
                }

                .document::before {
                    content: "";
                    position: absolute;
                    width: 420px;
                    height: 420px;
                    border-radius: 50%;
                    border: 1px solid rgba(214, 183, 108, 0.4);
                    top: -210px;
                    left: -130px;
                    box-shadow:
                        0 0 45px rgba(214, 183, 108, 0.65),
                        0 0 140px rgba(214, 183, 108, 0.35);
                    opacity: 0.9;
                    pointer-events: none;
                }

                .document::after {
                    content: "";
                    position: absolute;
                    width: 260px;
                    height: 260px;
                    border-radius: 50%;
                    border: 1px dashed rgba(60, 62, 74, 0.25);
                    bottom: -120px;
                    right: -60px;
                    box-shadow:
                        inset 0 0 40px rgba(60, 62, 74, 0.25),
                        0 0 80px rgba(214, 183, 108, 0.18);
                    opacity: 0.9;
                    pointer-events: none;
                }

                .layer {
                    position: relative;
                    z-index: 2;
                }

                .badge-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 18px;
                }

                .bureau-pill {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 6px 14px;
                    border-radius: 999px;
                    border: 1px solid #D1D4CD;
                    background: rgba(234, 235, 232, 0.95);
                    font-size: 9px;
                    letter-spacing: 2px;
                    text-transform: uppercase;
                    color: #3C3E4A;
                }

                .bureau-pill-dot {
                    width: 7px;
                    height: 7px;
                    border-radius: 50%;
                    background: #D6B76C;
                    box-shadow: 0 0 0 4px #F7F7F4;
                }

                .seal-id {
                    font-size: 9px;
                    letter-spacing: 1.8px;
                    text-transform: uppercase;
                    color: #777;
                }

                h1 {
                    margin: 0;
                    font-family: "DM Sans", system-ui, sans-serif;
                    font-size: 14px;
                    letter-spacing: 5px;
                    text-transform: uppercase;
                    color: #777;
                }

                .title-main {
                    margin: 6px 0 0 0;
                    font-family: "DM Sans", system-ui, sans-serif;
                    font-weight: 900;
                    font-size: 30px;
                    line-height: 1.05;
                    text-transform: uppercase;
                    letter-spacing: 3px;
                    color: #2F3140;
                }

                .title-main span {
                    display: block;
                    font-size: 48px;
                    letter-spacing: 2px;
                    background: linear-gradient(to bottom, #D6B76C, #3C3E4A 70%);
                    -webkit-background-clip: text;
                    background-clip: text;
                    color: transparent;
                }

                .subtitle {
                    margin-top: 10px;
                    font-size: 11px;
                    letter-spacing: 2.8px;
                    text-transform: uppercase;
                    color: #9A8A56;
                }

                .divider {
                    margin: 20px 0 18px 0;
                    height: 1px;
                    background: linear-gradient(to right,
                            rgba(60, 62, 74, 0.15),
                            rgba(214, 183, 108, 0.7),
                            rgba(60, 62, 74, 0.15));
                }

                .body-grid {
                    display: grid;
                    grid-template-columns: 3.1fr 1.7fr;
                    gap: 26px;
                }

                .body-main p {
                    margin: 0 0 10px 0;
                    font-size: 12px;
                    line-height: 1.8;
                    color: #333542;
                }

                ul {
                    margin: 8px 0 10px 18px;
                    padding: 0;
                    font-size: 11px;
                    line-height: 1.6;
                    color: #3F4150;
                }

                li {
                    margin-bottom: 4px;
                }

                .meta-line {
                    font-size: 11px;
                    color: #696B78;
                    margin-bottom: 6px;
                }

                .seraphic {
                    font-size: 11px;
                    font-style: italic;
                    color: #555764;
                    margin-top: 4px;
                }

                strong {
                    font-weight: 700;
                }

                em {
                    font-style: italic;
                }

                .side-panel {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-end;
                    justify-content: space-between;
                    gap: 18px;
                }

                .ledger-box {
                    border-radius: 12px;
                    border: 1px solid rgba(60, 62, 74, 0.25);
                    background: rgba(234, 235, 232, 0.95);
                    padding: 10px 12px;
                    font-size: 10px;
                    color: #3C3E4A;
                }

                .ledger-box-title {
                    font-size: 9px;
                    letter-spacing: 1.6px;
                    text-transform: uppercase;
                    margin-bottom: 4px;
                    color: #777;
                }

                .ledger-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 3px;
                }

                .ledger-label {
                    opacity: 0.8;
                }

                .ledger-value {
                    font-weight: 600;
                }

                .qr-block {
                    text-align: right;
                    margin-top: 10px;
                }

                .qr-note {
                    font-size: 9px;
                    color: #666876;
                    margin-bottom: 4px;
                }

                .footer {
                    margin-top: 18px;
                    padding-top: 10px;
                    border-top: 1px dashed rgba(60, 62, 74, 0.3);
                    font-size: 10px;
                    color: #6C6E7B;
                }
            </style>
        </head>

        <body>
            <div class="document">
                <div class="layer">
                    <div class="badge-row">
                        <div class="bureau-pill">
                            <span class="bureau-pill-dot"></span>
                            HEX &amp; HALO // OFFICE OF TINY WONDERS
                        </div>
                        <div class="seal-id">
                            PASS ID: MM-<?php echo strtoupper(substr(md5($name . $issueDate . $tier), 0, 6)); ?>
                        </div>
                    </div>

                    <h1>Miracle Ledger</h1>
                    <div class="title-main">
                        MINOR
                        <span style="color:rgba(214, 183, 108, 0.65) !important;">MIRACLES PASS</span>
                    </div>
                    <div class="subtitle">
                        CONTROLLED REALITY ADJUSTMENTS, UNDER ANGELIC OBSERVATION
                    </div>

                    <div class="divider"></div>

                    <div class="body-grid">
                        <div class="body-main">
                            <p>
                                This document attests that
                                <strong><?php echo htmlspecialchars($name); ?></strong>
                                has been granted
                                <strong><?php echo $tierInfo['count']; ?> minor miracles</strong>,
                                redeemable at any point before
                                <strong><?php echo $expiry; ?></strong>.
                            </p>

                            <p class="meta-line">
                                Issued on <strong><?php echo $issueDate; ?></strong> in the presence of low‑ranking
                                cherubim whose many eyes will quietly witness each miracle expended.
                            </p>

                            <?php if ($tier === 'tiny'): ?>
                                <p>
                                    The <strong>Tiny Wonders</strong> tier handles soft ripples in the timeline:
                                </p>
                                <ul>
                                    <li>Coin flips landing just right when the stakes are small but your heart cares.</li>
                                    <li>Lost‑then‑found keys, cards, and headphones returning on suspiciously perfect timing.</li>
                                    <li>Playlists aligning eerily well with your mood, as if curated by a glowing orb.</li>
                                </ul>
                                <p class="seraphic">
                                    These miracles are overseen by humming spheres of light who approve of wholesome convenience
                                    and extremely mild chaos.
                                </p>

                            <?php elseif ($tier === 'standard'): ?>
                                <p>
                                    The <strong>Standard Wonders</strong> tier authorizes practical reality edits:
                                </p>
                                <ul>
                                    <li>Conversations drifting away from conflict toward strangely satisfying resolutions.</li>
                                    <li>Exam questions and workloads leaning toward the topics you actually reviewed.</li>
                                    <li>
                                        Plans collapsing just in time to reveal a replacement option that fits uncannily well.
                                    </li>
                                </ul>
                                <p class="seraphic">
                                    Minor seraphim — wheels, wings, and too many eyes — monitor usage to ensure the chaos bends
                                    around you instead of straight through you.
                                </p>

                            <?php elseif ($tier === 'extra'): ?>
                                <p>
                                    The <strong>Extra Sparkly</strong> tier is reserved for cinematic timelines:
                                </p>
                                <ul>
                                    <li>
                                        Coincidences sharp enough that it feels like the script was rewritten mid‑scene
                                        in your favor.
                                    </li>
                                    <li>Lighting, weather, and ambience conspiring to give you unmistakable main‑character energy.</li>
                                    <li>
                                        Messages, encounters, and opportunities arriving at the exact moment they will land hardest.
                                    </li>
                                </ul>
                                <p class="seraphic">
                                    Higher‑order, biblically accurate attendants — rings of flame thick with eyes —
                                    take personal interest in your subplot and keep a glowing ledger of each miracle.
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="side-panel">
                            <div class="ledger-box">
                                <div class="ledger-box-title">ALLOCATION SUMMARY</div>
                                <div class="ledger-row">
                                    <span class="ledger-label">Bearer</span>
                                    <span class="ledger-value"><?php echo htmlspecialchars($name); ?></span>
                                </div>
                                <div class="ledger-row">
                                    <span class="ledger-label">Tier</span>
                                    <span class="ledger-value"><?php echo htmlspecialchars($tierInfo['label']); ?></span>
                                </div>
                                <div class="ledger-row">
                                    <span class="ledger-label">Miracles</span>
                                    <span class="ledger-value"><?php echo $tierInfo['count']; ?></span>
                                </div>
                                <div class="ledger-row">
                                    <span class="ledger-label">Valid until</span>
                                    <span class="ledger-value"><?php echo $expiry; ?></span>
                                </div>
                            </div>

                            <?php if ($tier === 'extra'): ?>
                                <div class="qr-block">
                                    <div class="qr-note">
                                        Extra Sparkly tier – scan to confirm miracle ledger in the Hex &amp; Halo registry.
                                    </div>
                                    <?php echo $qrImgTag; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="footer">
                        Miracles are non-transferable, subject to cosmic availability, and may not be redeemed for
                        cash, glory, or guaranteed parking. Overuse may attract curious angels: eldritch, many‑eyed,
                        and ultimately rooting for you.
                    </div>
                </div>
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
