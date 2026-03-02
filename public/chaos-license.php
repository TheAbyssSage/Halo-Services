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
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <title>Chaos Containment License</title>
            <style>
                body {
                    margin: 0;
                    padding: 24px;
                    font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
                    /* background-color: #080910; */
                    background-image:
                        radial-gradient(circle at top left, #3C3E4A 0, #080910 55%),
                        radial-gradient(circle at bottom right, #20222E 0, #080910 60%);
                    color: #F7F7F4;
                }

                .document {
                    position: relative;
                    box-sizing: border-box;
                    background: #F7F7F4;
                    color: #1F202B;
                    border-radius: 16px;
                    border: 1px solid #D1D4CD;
                    padding: 26px 28px 20px 28px;
                    box-shadow:
                        0 0 0 4px #EAEBE8,
                        0 16px 50px rgba(0, 0, 0, 0.7);
                    overflow: hidden;
                }

                .document::before {
                    content: "";
                    position: absolute;
                    width: 360px;
                    height: 360px;
                    border-radius: 50%;
                    border: 1px solid rgba(214, 183, 108, 0.45);
                    top: -190px;
                    right: -110px;
                    box-shadow:
                        0 0 40px rgba(214, 183, 108, 0.8),
                        0 0 120px rgba(214, 183, 108, 0.5);
                    opacity: 0.9;
                    pointer-events: none;
                }

                .document::after {
                    content: "";
                    position: absolute;
                    width: 220px;
                    height: 220px;
                    border-radius: 50%;
                    border: 1px dashed rgba(60, 62, 74, 0.35);
                    bottom: -100px;
                    left: -50px;
                    box-shadow:
                        inset 0 0 30px rgba(60, 62, 74, 0.5),
                        0 0 70px rgba(214, 183, 108, 0.2);
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
                    margin-bottom: 10px;
                }

                .bureau-pill {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 4px 11px;
                    border-radius: 999px;
                    border: 1px solid #D1D4CD;
                    background: linear-gradient(to right,
                            rgba(234, 235, 232, 0.98),
                            rgba(214, 183, 108, 0.25));
                    font-size: 8px;
                    letter-spacing: 2px;
                    text-transform: uppercase;
                    color: #262735;
                }

                .bureau-pill-dot {
                    width: 6px;
                    height: 6px;
                    border-radius: 50%;
                    background: #D6B76C;
                    box-shadow: 0 0 0 3px #F7F7F4;
                }

                .seal-id {
                    font-size: 8px;
                    letter-spacing: 1.6px;
                    text-transform: uppercase;
                    color: #777;
                }

                h1 {
                    margin: 0;
                    font-family: "DM Sans", system-ui, sans-serif;
                    font-size: 12px;
                    letter-spacing: 4px;
                    text-transform: uppercase;
                    color: #7A7070;
                }

                .title-main {
                    margin: 4px 0 0 0;
                    font-family: "DM Sans", system-ui, sans-serif;
                    font-weight: 900;
                    font-size: 24px;
                    line-height: 1.05;
                    text-transform: uppercase;
                    letter-spacing: 3px;
                    color: #242533;
                }

                .title-main span {
                    display: block;
                    font-size: 36px;
                    letter-spacing: 2px;
                    background: linear-gradient(to bottom, #D6B76C, #3C3E4A 75%);
                    -webkit-background-clip: text;
                    background-clip: text;
                    color: transparent;
                }

                .subtitle {
                    margin-top: 6px;
                    font-size: 9px;
                    letter-spacing: 2.4px;
                    text-transform: uppercase;
                    color: #A15B5B;
                }

                .divider {
                    margin: 12px 0;
                    height: 1px;
                    background: linear-gradient(to right,
                            rgba(60, 62, 74, 0.2),
                            rgba(214, 183, 108, 0.8),
                            rgba(60, 62, 74, 0.2));
                }

                .body {
                    font-size: 11px;
                    line-height: 1.7;
                    color: #323444;
                }

                .body p {
                    margin: 0 0 8px 0;
                }

                ul {
                    margin: 6px 0 8px 16px;
                    padding: 0;
                    font-size: 10.5px;
                    line-height: 1.5;
                    color: #3F4150;
                }

                li {
                    margin-bottom: 3px;
                }

                .meta-line {
                    font-size: 10px;
                    color: #696B78;
                    margin-bottom: 6px;
                }

                .eldritch {
                    font-size: 10px;
                    font-style: italic;
                    color: #555764;
                    margin-top: 3px;
                }

                strong {
                    font-weight: 700;
                }

                em {
                    font-style: italic;
                }

                .sigil-row {
                    display: flex;
                    align-items: flex-start;
                    margin-top: 8px;
                    gap: 10px;
                }

                .sigil-wrapper {
                    position: relative;
                    width: 90px;
                    height: 90px;
                    flex-shrink: 0;
                    border-radius: 50%;
                    border: 1px solid rgba(60, 62, 74, 0.5);
                    box-shadow:
                        0 0 0 4px #F7F7F4,
                        0 0 22px rgba(214, 183, 108, 0.85);
                    overflow: hidden;
                    background: radial-gradient(circle at center, #EAEBE8 0, #B0B2AC 50%, #EAEBE8 100%);
                }

                .sigil-ring {
                    position: absolute;
                    inset: 13%;
                    border-radius: 50%;
                    border: 1px solid rgba(60, 62, 74, 0.6);
                    box-shadow: inset 0 0 14px rgba(60, 62, 74, 0.6);
                }

                .sigil-ring::before,
                .sigil-ring::after {
                    content: "";
                    position: absolute;
                    border-radius: 50%;
                    border: 1px solid rgba(214, 183, 108, 0.75);
                }

                .sigil-ring::before {
                    inset: 20%;
                    box-shadow:
                        0 0 10px rgba(214, 183, 108, 0.9),
                        0 0 20px rgba(214, 183, 108, 0.7);
                }

                .sigil-ring::after {
                    inset: 42%;
                    border-style: dashed;
                    opacity: 0.9;
                }

                .sigil-eye {
                    position: absolute;
                    inset: 42%;
                    border-radius: 50%;
                    background: radial-gradient(circle at 30% 30%, #FFFFFF 0, #D6B76C 40%, #3C3E4A 92%);
                    box-shadow:
                        0 0 10px rgba(0, 0, 0, 0.55),
                        0 0 20px rgba(0, 0, 0, 0.45);
                }

                .sigil-eye::before,
                .sigil-eye::after {
                    content: "";
                    position: absolute;
                    border-radius: 50%;
                    border: 1px solid rgba(234, 235, 232, 0.95);
                }

                .sigil-eye::before {
                    inset: 18%;
                }

                .sigil-eye::after {
                    inset: 36%;
                }

                .sigil-text {
                    font-size: 9px;
                    color: #676975;
                }

                .qr-block {
                    margin-top: 8px;
                    text-align: right;
                }

                .qr-note {
                    font-size: 8.5px;
                    color: #666876;
                    margin-bottom: 3px;
                }

                .warning-box {
                    margin-top: 8px;
                    border-radius: 8px;
                    border: 1px dashed rgba(161, 91, 91, 0.7);
                    background: rgba(250, 232, 232, 0.96);
                    padding: 6px 8px;
                    font-size: 9px;
                    color: #4A3131;
                }

                .warning-title {
                    text-transform: uppercase;
                    letter-spacing: 1.4px;
                    font-size: 8px;
                    margin-bottom: 3px;
                    color: #8A3B3B;
                }

                .footer {
                    margin-top: 10px;
                    padding-top: 8px;
                    border-top: 1px dashed rgba(60, 62, 74, 0.3);
                    font-size: 9px;
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
                            HEX &amp; HALO // DEPARTMENT OF CONTAINED NONSENSE
                        </div>
                        <div class="seal-id">
                            LICENSE ID: CL-<?php echo strtoupper(substr(md5($name . $issueDate . $tier), 0, 6)); ?>
                        </div>
                    </div>

                    <h1>Operational Clearance</h1>
                    <div class="title-main">
                        CHAOS
                        <span style="color:rgba(214, 183, 108, 0.65) !important;">CONTAINMENT LICENSE</span>
                    </div>
                    <div class="subtitle">
                        AUTHORIZED MISCHIEF UNDER SERAPHIC SURVEILLANCE
                    </div>

                    <div class="divider"></div>

                    <div class="body">
                        <p>
                            This license is hereby issued to
                            <strong><?php echo htmlspecialchars($name); ?></strong>
                            and authorises the holder to engage in
                            <strong><?php echo htmlspecialchars($tierInfo['desc']); ?></strong>,
                            effective
                            <strong><?php echo $issueDate; ?></strong>
                            and valid until
                            <strong><?php echo $expiry; ?></strong>.
                        </p>

                        <p class="meta-line">
                            All activity is logged in the presence of supervisory entities resembling spinning halos,
                            burning wheels, and far too many eyes — indulgently amused, yet painfully diligent.
                        </p>

                        <?php if ($tier === 'apprentice'): ?>
                            <p>
                                As an <strong>Apprentice Gremlin</strong>, the bearer is cleared for carefully fenced chaos:
                            </p>
                            <ul>
                                <li>Harmless pranks with obvious punchlines and minimal collateral confusion.</li>
                                <li>Minor schedule shuffles, misplaced items, and brief, reversible anomalies.</li>
                                <li>Shenanigans conducted under distant observation from a single highly patient seraph.</li>
                            </ul>
                            <p class="eldritch">
                                You may feel a sudden “maybe don’t” at the edge of your plans; that is your angel
                                refusing to watch another preventable disaster.
                            </p>

                        <?php elseif ($tier === 'licensed'): ?>
                            <p>
                                As a <strong>Licensed Chaos Gremlin</strong>, the bearer may orchestrate mid‑level disturbance:
                            </p>
                            <ul>
                                <li>Timeline nudges resulting in improbable coincidences and suspiciously well‑timed delays.</li>
                                <li>Harmless social drama calibrated to end in laughter, not scorched friendships.</li>
                                <li>Independent operations within Hex &amp; Halo protocols, under silent balcony‑seated auditors.</li>
                            </ul>
                            <p class="eldritch">
                                Expect the sense of being watched by something with feathers and a clipboard
                                whenever you consider “just how bad could it be?”
                            </p>

                        <?php elseif ($tier === 'overseer'): ?>
                            <p>
                                As a <strong>Chaos Overseer</strong>, the bearer is entrusted with high‑risk, high‑style nonsense:
                            </p>
                            <ul>
                                <li>Multi‑stage chaos events spanning days, rooms, group chats, and timelines.</li>
                                <li>Drafting new prank protocols and narrative detours for future gremlins to follow.</li>
                                <li>Balancing delightful mayhem against genuine disaster, under direct seraphic scrutiny.</li>
                            </ul>
                            <p class="eldritch">
                                High‑order, biblically accurate auditors — rings of eyes and flame — consider your work
                                “interesting,” which is both a compliment and a warning.
                            </p>
                        <?php endif; ?>

                        <div class="sigil-row">
                            <div class="sigil-wrapper">
                                <div class="sigil-ring"></div>
                                <div class="sigil-eye"></div>
                            </div>
                            <div class="sigil-text">
                                Chaos sigil bound to <strong><?php echo htmlspecialchars($name); ?></strong>.<br>
                                Forgery attempts will be noticed by something with too many wings and
                                absolutely no sense of humor about paperwork.
                            </div>
                        </div>

                        <?php if ($tier === 'overseer'): ?>
                            <div class="qr-block">
                                <div class="qr-note">
                                    Overseer tier – scan to confirm containment protocols
                                    and view active incident log.
                                </div>
                                <?php echo $qrImgTag; ?>
                            </div>
                        <?php endif; ?>

                        <div class="warning-box">
                            <div class="warning-title">CONTAINMENT ADVISORY</div>
                            Do not deploy licensed chaos:
                            in hospitals, at funerals, in exam halls (without explicit angelic clearance),
                            or during active apocalypses. Failure to comply may result in revoked license,
                            stern wing‑fluffing, and awkward celestial meetings about your behaviour.
                        </div>

                        <div class="footer">
                            The Halo Bureau assumes no responsibility for property damage, confused bystanders,
                            spontaneous glitter storms, or memes escaping containment. License is void if used
                            during a full moon without a notarised permission slip signed by at least one entity
                            historically introduced with “do not be afraid.”
                        </div>
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
