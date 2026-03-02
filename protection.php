<?php
// public/protection.php
session_start();

require __DIR__ . '/vendor/autoload.php';

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
                /* Base page */
                body {
                    margin: 0;
                    padding: 48px;
                    font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
                    background-color: #181922;
                    /* subtle paper texture effect via gradient */
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

                /* Halo ring & sigil in background */
                .document::before {
                    content: "";
                    position: absolute;
                    width: 460px;
                    height: 460px;
                    border-radius: 50%;
                    border: 1px solid rgba(214, 183, 108, 0.4);
                    top: -220px;
                    right: -120px;
                    box-shadow:
                        0 0 45px rgba(214, 183, 108, 0.65),
                        0 0 140px rgba(214, 183, 108, 0.35);
                    opacity: 0.8;
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

                /* Common layers */
                .layer {
                    position: relative;
                    z-index: 2;
                }

                /* Header */
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
                    font-size: 34px;
                    line-height: 1.05;
                    text-transform: uppercase;
                    letter-spacing: 3px;
                    color: #2F3140;
                }

                .title-main span {
                    display: block;
                    font-size: 54px;
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

                /* Divider */
                .divider {
                    margin: 20px 0 18px 0;
                    height: 1px;
                    background: linear-gradient(to right,
                            rgba(60, 62, 74, 0.15),
                            rgba(214, 183, 108, 0.7),
                            rgba(60, 62, 74, 0.15));
                }

                /* Body layout */
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

                strong {
                    font-weight: 700;
                }

                em {
                    font-style: italic;
                }

                /* Side column / sigil */
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

                /* QR block (only used for archangel) */
                .qr-block {
                    text-align: right;
                    margin-top: 10px;
                }

                .qr-note {
                    font-size: 9px;
                    color: #666876;
                    margin-bottom: 4px;
                }

                /* Footer */
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
                            HEX &amp; HALO // BUREAU OF CELESTIAL AFFAIRS
                        </div>
                        <div class="seal-id">
                            <br>
                            SEAL ID: AP-<?php echo strtoupper(substr(md5($name . $startDate . $tier), 0, 6)); ?>
                        </div>
                    </div>

                    <h1>Official Assignment</h1>
                    <div class="title-main">
                        ANGELIC
                        <span style="color:rgba(214, 183, 108, 0.65) !important;">PROTECTION SEAL</span>
                    </div>
                    <div class="subtitle">
                        ISSUED IN THE PRESENCE OF BIBLICALLY ACCURATE ANGELS
                    </div>

                    <div class="divider"></div>

                    <div class="body-grid">
                        <div class="body-main">
                            <p>
                                Let it be known that
                                <strong><?php echo htmlspecialchars($name); ?></strong>
                                is placed under sanctioned protection from
                                <strong><?php echo htmlspecialchars($startDate); ?></strong>
                                to
                                <strong><?php echo htmlspecialchars($endDate); ?></strong>.
                            </p>

                            <p class="meta-line">
                                Protection tier:
                                <em><?php echo htmlspecialchars($tierInfo['label']); ?> – <?php echo htmlspecialchars($tierInfo['desc']); ?></em>.
                            </p>

                            <?php if ($tier === 'light'): ?>
                                <p>
                                    Under the <strong>Light Ward</strong>, a quiet orbit of gentle, many‑eyed guardians
                                    keeps its distance at the edge of your story, intervening only when the odds
                                    lean too sharply toward petty misfortune.
                                </p>
                                <ul>
                                    <li>Deflecting low‑grade malice, envy, and background bad vibes.</li>
                                    <li>Sanding down the rough edges of everyday stress and social awkwardness.</li>
                                    <li>Subtly tilting decisions away from the most cursed possible timelines.</li>
                                </ul>
                                <p class="eldritch">
                                    These entities resemble rings of soft light and feathered geometry.
                                    They are, technically, terrifying — but have been thoroughly briefed
                                    on “being gentle” with mortals.
                                </p>

                            <?php elseif ($tier === 'standard'): ?>
                                <p>
                                    Under the <strong>Standard Ward</strong>, a small choir of biblically accurate angels
                                    rotates around your path: wheels within wheels, eyes in every direction,
                                    all quietly auditing the dangers you don’t see.
                                </p>
                                <ul>
                                    <li>Intercepting near‑miss accidents and turning them into strange, safe anecdotes.</li>
                                    <li>Blunting hostile encounters into baffling but mostly harmless interactions.</li>
                                    <li>Lighting crucial choices from impossible angles so your better options stand out.</li>
                                </ul>
                                <p class="eldritch">
                                    Glimpses of their presence may appear as impossible reflections, sudden stillness,
                                    or the sense that time “caught” you before you tripped.
                                    This is considered within operational parameters.
                                </p>

                            <?php elseif ($tier === 'archangel'): ?>
                                <p>
                                    Under the <strong>Archangel Priority</strong> tier, high‑ranking, lore‑accurate beings
                                    are formally assigned to your case file. They are older than language,
                                    brighter than good sense, and very invested in you not getting obliterated
                                    by narrative difficulty spikes.
                                </p>
                                <ul>
                                    <li>
                                        Automatic escalation of critical threats to entities composed of wings,
                                        halos, and voices that begin with “do not be afraid.”
                                    </li>
                                    <li>
                                        Boss fights, exam weeks, and turning‑point conversations granted
                                        extra layers of invisible armor and improbable grace.
                                    </li>
                                    <li>
                                        Active mitigation of despair, doom spirals, and self‑sabotage,
                                        enforced by eyes that remember more futures than you can count.
                                    </li>
                                </ul>
                                <p class="eldritch">
                                    Side effects may include vivid dreams of orbiting halos,
                                    sudden calm at the exact center of chaos, and the quiet conviction
                                    that something enormous and kind just nodded in your direction.
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="side-panel">
                            <div>
                                <!-- <div class="sigil-wrapper">
                                    <div class="sigil-ring"></div>
                                    <div class="sigil-eye"></div>
                                </div> -->
                                <div class="side-caption">
                                    Official seraphic sigil registered to
                                    <strong><?php echo htmlspecialchars($name); ?></strong>.
                                    Tampering voids protection and annoys everyone involved.
                                </div>
                            </div>

                            <?php if ($tier === 'archangel'): ?>
                                <div class="qr-block">
                                    <div class="qr-note">
                                        Archangel tier – scan to access celestial dispatch log for this assignment.
                                    </div>
                                    <?php echo $qrImgTag; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="footer">
                        Angels are on standby. Do not deliberately provoke entities historically introduced with
                        “do not be afraid” unless absolutely necessary. Hex &amp; Halo accepts no liability
                        for voluntary recklessness, unsanctioned demon negotiations, or attempts to look
                        directly at anything with more than four wings.
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
<div class="cert-hero">
    <div class="cert-hero-icon">⛨</div>
    <h1>Angelic <span>Protection</span> Seal</h1>
    <p class="cert-hero-sub">
        A one‑month ward against cursed timing and narrative difficulty spikes. From gentle background
        guardianship to full exam‑week archangel priority, signed off by the Bureau of Celestial Affairs.
    </p>
    <div class="cert-hero-meta">
        <div class="cert-pill">30‑day coverage</div>
        <div class="cert-pill">tiered seraphic support</div>
        <div class="cert-pill">biblically accurate oversight</div>
    </div>
</div>

<div class="cert-lore">
    <h2>How this ward behaves</h2>
    <p>
        Your name goes into a very serious ledger watched by beings made of halos, fire,
        and far too many eyes. For one month, they quietly interfere whenever the universe
        is about to get a little too dramatic on your watch.
    </p>
    <p>
        Think fewer catastrophic near‑misses, more “that could have gone so much worse” moments,
        and the occasional suspiciously well‑timed save from something with wings.
    </p>
</div>

<div class="cert-tiers">
    <div class="cert-tier-card">
        <div class="cert-tier-label">Soft mode</div>
        <div class="cert-tier-main">
            <div class="cert-tier-name">Light Ward</div>
            <div class="cert-tier-count">€3.99</div>
        </div>
        <div class="cert-tier-desc">
            Gentle nudge against bad vibes, petty misfortune, and small daily chaos.
        </div>
        <div class="cert-tier-price">Best for “life is mostly fine but I’d like a buff.”</div>
    </div>

    <div class="cert-tier-card highlight">
        <div class="cert-tier-label">Everyday shield</div>
        <div class="cert-tier-main">
            <div class="cert-tier-name">Standard Ward</div>
            <div class="cert-tier-count">€6.99</div>
        </div>
        <div class="cert-tier-desc">
            Choir of angels quietly auditing your timeline and intercepting spicier nonsense.
        </div>
        <div class="cert-tier-price">Recommended default setting.</div>
    </div>

    <div class="cert-tier-card" style="grid-column: 1 / -1;">
        <div class="cert-tier-label">Boss fight prep</div>
        <div class="cert-tier-main">
            <div class="cert-tier-name">Archangel Priority</div>
            <div class="cert-tier-count">€12.99</div>
        </div>
        <div class="cert-tier-desc">
            High‑ranking, lore‑accurate angels assigned to your case file for big moments.
        </div>
        <div class="cert-tier-price">
            Exam weeks, hard conversations, and “please don’t let this explode” months.
        </div>
    </div>
</div>

<div class="cert-form-section">
    <h2>Request your seal</h2>
    <p style="font: 12px/18px 'Inter', sans-serif; color: #666; margin-bottom: 14px;">
        Tell the Bureau who you are and when the ward should start. We’ll mint a personalised
        PDF with IDs, dates, and just enough eldritch fine print to feel official.
    </p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="protection.php" class="mb-4">
        <div class="mb-3">
            <label class="form-label">
                Your name
                <span style="color:#888; font-size:11px;">(for the celestial case file)</span>
            </label>
            <input type="text" name="name" class="form-control"
                value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Protection tier</label>
            <select name="tier" class="form-select">
                <option value="light" <?php echo (($_POST['tier'] ?? '') === 'light') ? 'selected' : ''; ?>>
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
            <label class="form-label">
                Start date
                <span style="color:#888; font-size:11px;">(when the ward goes live)</span>
            </label>
            <input type="date" name="start_date" class="form-control"
                value="<?php echo htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')); ?>">
        </div>

        <button type="submit" class="btn btn-dark btn-sm">Add to cart ⛨</button>
    </form>
</div>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Angelic Protection';
$activePage = 'certificate';
include __DIR__ . '/layout.php';
