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
                    background: radial-gradient(circle at top right, rgba(214, 183, 108, 0.26), transparent 60%);
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

                .eldritch {
                    font-style: italic;
                    color: #555;
                    font-size: 11px;
                }
            </style>
        </head>

        <body>
            <div class="card">
                <div class="pill">Hex &amp; Halo // Department of Contained Nonsense</div>

                <h1>Chaos Containment License</h1>
                <h2><?php echo htmlspecialchars($tierInfo['label']); ?></h2>

                <p>
                    This license is issued to
                    <strong><?php echo htmlspecialchars($name); ?></strong>
                    and authorises the holder to engage in
                    <strong><?php echo htmlspecialchars($tierInfo['desc']); ?></strong>,
                    effective
                    <strong><?php echo $issueDate; ?></strong>
                    and valid until
                    <strong><?php echo $expiry; ?></strong>.
                </p>

                <p class="meta">
                    All activities are logged in the presence of supervisory entities resembling
                    spinning halos, burning wheels, and far too many eyes, who find your antics entertaining.
                </p>

                <?php if ($tier === 'apprentice'): ?>
                    <p>
                        As an <strong>Apprentice Gremlin</strong>, the bearer is cleared for:
                    </p>
                    <ul>
                        <li>Harmless pranks with easy cleanup and obvious punchlines.</li>
                        <li>Minor schedule shuffles, misplaced items, and brief, harmless confusion.</li>
                        <li>
                            Shenanigans performed under loose observation from a distant, many‑eyed overseer
                            who will intervene if it gets too spicy.
                        </li>
                    </ul>
                    <p class="eldritch">
                        Angels assigned to this tier primarily watch, take notes, and occasionally
                        mutter “maybe not that one” into your subconscious.
                    </p>
                <?php elseif ($tier === 'licensed'): ?>
                    <p>
                        As a <strong>Licensed Chaos Gremlin</strong>, the bearer may:
                    </p>
                    <ul>
                        <li>
                            Initiate medium‑level chaos events:
                            unexpected plot twists, schedule scrambles, and improbable coincidences.
                        </li>
                        <li>
                            Stir harmless drama in social situations, provided it resolves into laughter
                            rather than scorched earth.
                        </li>
                        <li>
                            Operate independently within Hex &amp; Halo protocols, under passive angelic
                            supervision from the rafters of reality.
                        </li>
                    </ul>
                    <p class="eldritch">
                        Expect the occasional chill down your spine when you almost go too far:
                        that is a seraph gently applying the brakes.
                    </p>
                <?php elseif ($tier === 'overseer'): ?>
                    <p>
                        As a <strong>Chaos Overseer</strong>, the bearer is entrusted with:
                    </p>
                    <ul>
                        <li>Coordinating multi‑stage nonsense across rooms, days, and group chats.</li>
                        <li>Authoring new prank protocols, narrative detours, and side quests.</li>
                        <li>
                            Maintaining delicate balance between delightful chaos and actual disaster,
                            under direct supervision of high‑order, biblically accurate auditors.
                        </li>
                    </ul>

                    <p class="eldritch">
                        These auditors are rings of eyes and flame who appreciate artful mischief
                        but will absolutely intervene if you risk tearing the vibe in half.
                    </p>

                    <div class="qr-wrap">
                        <div class="qr-note">
                            Overseer tier – scan to confirm containment protocols and active case file.
                        </div>
                        <?php echo $qrImgTag; ?>
                    </div>
                <?php endif; ?>

                <p class="footer">
                    The Halo Bureau assumes no responsibility for property damage, confused bystanders,
                    spontaneous glitter, or memes escaping containment. License is void if used during
                    a full moon without a notarised permission slip signed by at least one winged entity
                    who has seen the end of the world and still thinks you’re funny.
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
