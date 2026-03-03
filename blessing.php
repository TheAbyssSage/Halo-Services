<?php
session_start();

require __DIR__ . '/vendor/autoload.php';

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
                    width: 440px;
                    height: 440px;
                    border-radius: 50%;
                    border: 1px solid rgba(214, 183, 108, 0.45);
                    top: -220px;
                    right: -140px;
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
                    left: -60px;
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
                    font-size: 32px;
                    line-height: 1.05;
                    text-transform: uppercase;
                    letter-spacing: 3px;
                    color: #2F3140;
                }

                .title-main span {
                    display: block;
                    font-size: 52px;
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
                    grid-template-columns: 3.2fr 1.6fr;
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

                .eldritch {
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

                .sigil-wrapper {
                    position: relative;
                    width: 150px;
                    height: 150px;
                    border-radius: 50%;
                    border: 1px solid rgba(60, 62, 74, 0.35);
                    box-shadow:
                        0 0 0 8px #F7F7F4,
                        0 0 32px rgba(214, 183, 108, 0.6);
                    overflow: hidden;
                    background: radial-gradient(circle at center, #EAEBE8 0, #D1D4CD 50%, #EAEBE8 100%);
                }

                .sigil-ring {
                    position: absolute;
                    inset: 14%;
                    border-radius: 50%;
                    border: 1px solid rgba(60, 62, 74, 0.45);
                    box-shadow: inset 0 0 18px rgba(60, 62, 74, 0.4);
                }

                .sigil-ring::before,
                .sigil-ring::after {
                    content: "";
                    position: absolute;
                    border-radius: 50%;
                    border: 1px solid rgba(214, 183, 108, 0.6);
                }

                .sigil-ring::before {
                    inset: 20%;
                    box-shadow:
                        0 0 12px rgba(214, 183, 108, 0.8),
                        0 0 28px rgba(214, 183, 108, 0.5);
                }

                .sigil-ring::after {
                    inset: 42%;
                    border-style: dashed;
                    opacity: 0.8;
                }

                .sigil-eye {
                    position: absolute;
                    inset: 43%;
                    border-radius: 50%;
                    background: radial-gradient(circle at 30% 30%, #FFFFFF 0, #D6B76C 45%, #3C3E4A 90%);
                    box-shadow: 0 0 12px rgba(0, 0, 0, 0.45);
                }

                .sigil-eye::before,
                .sigil-eye::after {
                    content: "";
                    position: absolute;
                    border-radius: 50%;
                    border: 1px solid rgba(234, 235, 232, 0.9);
                }

                .sigil-eye::before {
                    inset: 18%;
                }

                .sigil-eye::after {
                    inset: 36%;
                }

                .side-caption {
                    margin-top: 10px;
                    font-size: 10px;
                    color: #676975;
                    text-align: right;
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
                            HEX &amp; HALO // BUREAU OF GENTLE MIRACLES
                        </div>
                        <div class="seal-id">
                            BLESSING ID: WF-<?php echo strtoupper(substr(md5($name . $startDate), 0, 6)); ?>
                        </div>
                    </div>

                    <h1>Temporal Notice</h1>
                    <div class="title-main">
                        CERTIFICATE OF
                        <span style="color:rgba(214, 183, 108, 0.65) !important;">DIVINE FAVOR</span>
                    </div>
                    <div class="subtitle">
                        WEEK‑LONG BLESSING – OBSERVED BY MANY‑EYED SERAPHS
                    </div>

                    <div class="divider"></div>

                    <div class="body-grid">
                        <div class="body-main">
                            <p>
                                Let it be recorded that
                                <strong><?php echo htmlspecialchars($name); ?></strong>
                                is placed under temporary, luminous favor from
                                <strong><?php echo htmlspecialchars($startDate); ?></strong>
                                until
                                <strong><?php echo htmlspecialchars($endDate); ?></strong>.
                            </p>

                            <p class="meta-line">
                                During this span, a small assembly of biblically accurate angels
                                (wheels within wheels, wings upon wings, eyes in every direction)
                                has been instructed to lean gently on probability on the bearer’s behalf.
                            </p>

                            <p>
                                Expected manifestations may include:
                            </p>
                            <ul>
                                <li>Moments of uncanny timing where doors open just as you arrive.</li>
                                <li>Chance encounters that feel suspiciously scripted in your favor.</li>
                                <li>A measurable reduction in cursed events, glitches, and dead‑end timelines.</li>
                            </ul>

                            <p class="eldritch">
                                Witnesses may report the distant rustle of wings, golden static in quiet rooms,
                                or the sensation of being calmly watched by something enormous and kind.
                                This is not a bug.
                            </p>

                            <p>
                                This certificate does not grant invincibility. It merely ensures that when
                                chaos rolls its dice, a quiet choir of halos nudges them to land
                                a little softer on your side.
                            </p>
                        </div>

                        <div class="side-panel">
                            <div>
                                <div class="sigil-wrapper">
                                    <div class="sigil-ring"></div>
                                    <div class="sigil-eye"></div>
                                </div>
                                <div class="side-caption">
                                    Favor sigil keyed to
                                    <strong><?php echo htmlspecialchars($name); ?></strong>.
                                    Transfer attempts will be politely rejected by several dozen wings.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="footer">
                        Issued by Hex &amp; Halo, Bureau of Gentle Miracles.
                        Valid for one mortal week only. Renewal requires additional petitions and at least one
                        aesthetically pleasing snack offering. Do not stare directly at anything introduced with
                        “do not be afraid” without emotional preparation.
                    </div>
                </div>
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
            'price'       => 1, // set your price
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
<div class="cert-hero">
    <div class="cert-hero-icon">✧</div>
    <h1>Weekly <span>Blessing</span> Infusion</h1>
    <p class="cert-hero-sub">
        A week-long spike of gentle, probability‑bending favor, issued by the Hex &amp; Halo Bureau
        of Gentle Miracles. Comes as an extremely printable PDF.
    </p>
    <div class="cert-hero-meta">
        <div class="cert-pill">7‑day coverage</div>
        <div class="cert-pill">cosmic morale boost</div>
        <div class="cert-pill">spiritually binding, legally decorative</div>
    </div>
</div>

<div class="cert-lore">
    <h2>What this blessing actually does</h2>
    <p>
        For one mortal week, a small choir of many‑eyed seraphs nudges the odds in your favour.
        Nothing huge, nothing apocalypse‑tier – just fewer cursed moments, softer landings,
        and a suspicious number of well‑timed little wins.
    </p>
    <p>
        Think: trains you just make, emails answered kindly, plans that almost fall apart but
        reassemble into something better. No guarantees, but the halos are quietly invested
        in your subplot not being entirely feral.
    </p>
</div>

<div class="cert-form-section">
    <h2>Prepare your blessing</h2>
    <p style="font: 12px/18px 'Inter', sans-serif; color: #666; margin-bottom: 14px;">
        Fill in your mortal details. We’ll mint a personalised PDF certificate, tuck a QR code
        into the corner, and drop it in your cart for celestial checkout.
    </p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="blessing.php" class="mb-4">
        <div class="mb-3">
            <label class="form-label">
                Your name
                <span style="color:#888; font-size:11px;">(for the blessing ledger)</span>
            </label>
            <input type="text" name="name" class="form-control"
                value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Start date</label>
            <input type="date" name="start_date" class="form-control"
                value="<?php echo htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')); ?>">
        </div>

        <button type="submit" class="btn btn-dark btn-sm">Prepare blessing ✧</button>
    </form>
</div>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Weekly Blessing';
$activePage = 'certificate';

include __DIR__ . '/layout.php';
