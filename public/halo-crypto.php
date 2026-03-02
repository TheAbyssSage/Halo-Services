<?php
// public/halo-crypto.php
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
    'sinner'    => [
        'label' => "Sinner's Stack",
        'price' => 2.99,
        'coins' => 1,
        'desc'  => "One coin. Barely blessed. Still more holy than your entire portfolio.",
    ],
    'disciple'  => [
        'label' => 'Disciple Bundle',
        'price' => 7.77,
        'coins' => 7,
        'desc'  => 'Seven coins — one for each day of creation. Very on-brand.',
    ],
    'apostle'   => [
        'label' => 'Apostle Reserve',
        'price' => 12.00,
        'coins' => 12,
        'desc'  => 'Twelve coins. One for each apostle. Yes, including that one.',
    ],
    'archangel' => [
        'label' => 'Archangel Vault',
        'price' => 33.33,
        'coins' => 33,
        'desc'  => 'Thirty-three coins. The holiest number. Do not ask why.',
    ],
    'omnipotent' => [
        'label' => 'Omnipotent Edition',
        'price' => 99.99,
        'coins' => 'ꝏ',
        'desc'  => 'Infinite coins. Conceptually. We cannot ship infinity but we will try.',
    ],
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $tier = $_POST['tier'] ?? 'disciple';
    if (!array_key_exists($tier, $tiers)) $tier = 'disciple';

    if ($name === '') {
        $error = 'Name is required to enter the blockchain of heaven.';
    } else {
        $tierInfo  = $tiers[$tier];
        $issueDate = date('Y-m-d');
        $certId    = strtoupper(substr(md5($name . time()), 0, 12));

        // QR
        $qrText   = 'HALO COIN CERTIFICATE – ' . $tierInfo['label'] . ' – Holder: ' . $name . ' – ID: ' . $certId;
        $qr       = new QrCode($qrText);
        $writer   = new PngWriter();
        $result   = $writer->write($qr);
        $qrBase64 = base64_encode($result->getString());
        $qrImgTag = '<img src="data:image/png;base64,' . $qrBase64 . '" width="100" alt="Halo QR">';

        $coinsDisplay = $tierInfo['coins'];

        ob_start();
?>
        <!DOCTYPE html>
        <html>

        <head>
            <style>
                body {
                    font-family: Georgia, serif;
                    padding: 40px;
                    color: #1a1a1a;
                    background: #fff;
                }

                .border-outer {
                    border: 4px double #c9a84c;
                    padding: 30px;
                }

                .border-inner {
                    border: 1px solid #c9a84c;
                    padding: 24px;
                }

                h1 {
                    font-size: 28px;
                    text-align: center;
                    text-transform: uppercase;
                    letter-spacing: 4px;
                    margin: 0 0 4px 0;
                }

                .subtitle {
                    text-align: center;
                    font-size: 11px;
                    letter-spacing: 6px;
                    text-transform: uppercase;
                    color: #888;
                    margin-bottom: 24px;
                }

                .seal {
                    text-align: center;
                    font-size: 48px;
                    margin: 10px 0;
                }

                p {
                    font-size: 13px;
                    line-height: 1.7;
                    margin: 10px 0;
                }

                .certid {
                    font-size: 10px;
                    color: #999;
                    font-family: monospace;
                    text-align: center;
                    margin-top: 20px;
                }

                .amount {
                    font-size: 22px;
                    font-weight: bold;
                    text-align: center;
                    margin: 16px 0;
                    color: #c9a84c;
                    letter-spacing: 2px;
                }

                .footer {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-end;
                    margin-top: 24px;
                }

                .sig {
                    text-align: left;
                }

                .sig div {
                    border-top: 1px solid #333;
                    padding-top: 6px;
                    font-size: 10px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    color: #555;
                    margin-top: 24px;
                }

                .fine-print {
                    font-size: 9px;
                    color: #aaa;
                    line-height: 1.4;
                    margin-top: 16px;
                    border-top: 1px solid #eee;
                    padding-top: 10px;
                }
            </style>
        </head>

        <body>
            <div class="border-outer">
                <div class="border-inner">
                    <!-- <div class="seal">✨ ✨ ✨</div> -->
                    <h1>Certificate of Holy Coin Ownership</h1>
                    <div class="subtitle">Issued by the Bureau of Divine Finance · Hex &amp; Halo</div>

                    <p>Let it be known throughout the mortal and celestial planes that</p>
                    <p style="font-size:18px; font-weight:bold; text-align:center;"><?php echo htmlspecialchars($name); ?></p>
                    <p>is hereby certified as the rightful and spiritually verified holder of:</p>

                    <div class="amount"><?php echo htmlspecialchars((string)$coinsDisplay); ?> HALO COIN<?php echo ($coinsDisplay !== 1) ? 'S' : ''; ?></div>

                    <p>
                        The <?php echo htmlspecialchars($tierInfo['label']); ?> — <?php echo htmlspecialchars($tierInfo['desc']); ?>
                    </p>

                    <p>
                        HALO Coin is the world's first, only, and holiest blockchain currency, minted directly from
                        celestial excess. Each coin is individually blessed by at least two (2) semi-qualified angels,
                        one (1) notarised cherub, and a monk who definitely knew what he was doing.
                        This certificate proves beyond reasonable cosmic doubt that the named holder possesses
                        the specified quantity of HALO, which exists simultaneously on the blockchain and also
                        in a realm you cannot access with a normal wallet.
                    </p>

                    <div class="footer">
                        <div class="sig">
                            <div>Archdirector of Heavenly Assets</div>
                        </div>
                        <div><?php echo $qrImgTag; ?></div>
                    </div>

                    <div class="certid">Certificate ID: <?php echo $certId; ?> · Issued: <?php echo $issueDate; ?></div>

                    <p class="fine-print">
                        HALO Coin is not legal tender in any earthly jurisdiction, purgatory, or the astral plane.
                        Hex &amp; Halo makes no guarantee that HALO Coin will appreciate in value, grant salvation,
                        or prevent mild misfortune. This certificate is spiritually binding but legally decorative.
                        Past holiness is not indicative of future holiness. Not financial advice. Especially not divine financial advice.
                        If your coin is lost, stolen, or ascended without you, we cannot help. Please do not pray to your wallet.
                    </p>
                </div>
            </div>
        </body>

        </html>
<?php
        $html = ob_get_clean();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        $tmpDir = __DIR__ . '/tmp_certs';
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);
        $fileName = 'halocoin-' . preg_replace('/\W+/', '-', strtolower($name)) . '-' . time() . '.pdf';
        file_put_contents($tmpDir . '/' . $fileName, $pdfOutput);

        $_SESSION['cart'][] = [
            'id'          => 'cert_halo_' . time(),
            'type'        => 'certificate',
            'certificate' => 'halo-crypto',
            'display'     => $tierInfo['label'] . ' – ' . $name,
            'price'       => $tierInfo['price'],
            'file_path'   => 'tmp_certs/' . $fileName,
            'meta'        => [
                'name'   => $name,
                'tier'   => $tier,
                'coins'  => $tierInfo['coins'],
                'certId' => $certId,
            ],
        ];

        header('Location: cart.php');
        exit;
    }
}

ob_start();
?>

<style>
    .hc-hero {
        background: linear-gradient(135deg, #3C3E4A 0%, #1a1b22 100%);
        border-radius: 14px;
        padding: 36px 28px 32px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        color: #F7F7F4;
    }

    .hc-hero::before {
        content: '';
        position: absolute;
        width: 320px;
        height: 320px;
        background: radial-gradient(circle, rgba(214, 183, 108, 0.25) 0%, transparent 70%);
        top: -80px;
        right: -60px;
        pointer-events: none;
    }

    .hc-hero-coin {
        font-size: 56px;
        line-height: 1;
        margin-bottom: 12px;
        position: relative;
        z-index: 1;
        animation: halo-spin 8s linear infinite;
        display: inline-block;
    }

    @keyframes halo-spin {
        0% {
            filter: drop-shadow(0 0 8px rgba(214, 183, 108, 0.8));
        }

        50% {
            filter: drop-shadow(0 0 22px rgba(214, 183, 108, 1));
        }

        100% {
            filter: drop-shadow(0 0 8px rgba(214, 183, 108, 0.8));
        }
    }

    .hc-hero h2 {
        font: 900 28px/30px 'DM Sans', sans-serif;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin: 0 0 8px 0;
        position: relative;
        z-index: 1;
    }

    .hc-hero h2 span {
        color: #D6B76C;
    }

    .hc-hero-sub {
        font: 12px/18px 'Inter', sans-serif;
        color: rgba(247, 247, 244, 0.65);
        max-width: 480px;
        position: relative;
        z-index: 1;
        margin: 0;
    }

    .hc-stats {
        display: flex;
        gap: 16px;
        margin-top: 20px;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .hc-stat {
        background: rgba(255, 255, 255, 0.07);
        border: 1px solid rgba(214, 183, 108, 0.25);
        border-radius: 10px;
        padding: 10px 14px;
        font: 500 11px/12px 'Inter', sans-serif;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(247, 247, 244, 0.7);
    }

    .hc-stat strong {
        display: block;
        font: 700 18px/20px 'DM Sans', sans-serif;
        color: #D6B76C;
        margin-bottom: 3px;
        letter-spacing: 0;
    }

    .hc-lore {
        border: 1px solid var(--bgBorder);
        border-radius: 12px;
        padding: 20px 22px;
        background: var(--bg0);
        margin-bottom: 24px;
        font: 13px/20px 'Inter', sans-serif;
        color: #444;
    }

    .hc-lore h3 {
        font: 700 13px/14px 'DM Sans', sans-serif;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 0 0 10px 0;
        color: #1a1a1a;
    }

    .hc-lore p {
        margin: 0 0 8px 0;
    }

    .hc-lore p:last-child {
        margin: 0;
    }

    .hc-tiers {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        margin-bottom: 24px;
    }

    @media (min-width: 576px) {
        .hc-tiers {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .hc-tier-card {
        border: 1px solid var(--bgBorder);
        border-radius: 12px;
        padding: 14px 16px;
        background: var(--bg0);
        position: relative;
        overflow: hidden;
        cursor: default;
    }

    .hc-tier-card.featured {
        border-color: #D6B76C;
        background: linear-gradient(135deg, #fffbf0, #fdf5de);
    }

    .hc-tier-label {
        font: 700 12px/14px 'DM Sans', sans-serif;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 4px;
    }

    .hc-tier-coins {
        font: 900 26px/28px 'DM Sans', sans-serif;
        color: #D6B76C;
        margin-bottom: 4px;
    }

    .hc-tier-desc {
        font: 11px/16px 'Inter', sans-serif;
        color: #666;
        margin-bottom: 10px;
    }

    .hc-tier-price {
        font: 700 14px/16px 'DM Sans', sans-serif;
        color: #1a1a1a;
    }

    .hc-form-section h3 {
        font: 700 13px/14px 'DM Sans', sans-serif;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 0 0 14px 0;
    }
</style>

<div class="hc-hero">
    <div class="hc-hero-coin">✦</div>
    <h2>HALO <span>Coin</span></h2>
    <p class="hc-hero-sub">
        The world's first, only, and definitively holiest cryptocurrency. Minted in the upper celestial layers.
        Verified by beings with very good credit scores.
    </p>
    <div class="hc-stats">
        <div class="hc-stat"><strong>∞</strong> Total Supply</div>
        <div class="hc-stat"><strong>XIV</strong> Angel Validators</div>
        <div class="hc-stat"><strong>0</strong> Rug Pulls (so far)</div>
        <div class="hc-stat"><strong>100%</strong> Blessed</div>
    </div>
</div>

<div class="hc-lore">
    <h3>What is HALO Coin?</h3>
    <p>
        HALO Coin is a spiritually-backed digital asset operating on the <strong>HeavenChain™ protocol</strong> —
        a proprietary blockchain secured by cryptographic prayer and verified by a decentralised network of
        semi-retired archangels. Unlike earthly currencies, HALO cannot be inflated, deflated, or used at
        an ATM, which frankly makes it more trustworthy than most central banks.
    </p>
    <p>
        Each coin is minted during a full moon by a licensed celestial minter using only renewable
        divine energy. The carbon footprint is negative, because every transaction plants a metaphorical
        tree in a realm you cannot visit. HALO is the first cryptocurrency to be simultaneously on the
        blockchain AND in a state of grace.
    </p>
    <p>
        <em>Note: HALO Coin is currently in its Pre-Rapture ICO phase. Coins are real. The blockchain is
            coming. Your faith is the whitepaper.</em>
    </p>
</div>

<div class="hc-tiers">
    <div class="hc-tier-card">
        <div class="hc-tier-label">Sinner's Stack</div>
        <div class="hc-tier-coins">1 ✦</div>
        <div class="hc-tier-desc">One coin. Barely blessed. Still more holy than your entire portfolio.</div>
        <div class="hc-tier-price">€2.99</div>
    </div>
    <div class="hc-tier-card featured">
        <div class="hc-tier-label">✦ Disciple Bundle</div>
        <div class="hc-tier-coins">7 ✦</div>
        <div class="hc-tier-desc">Seven coins — one for each day of creation. Very on-brand.</div>
        <div class="hc-tier-price">€7.77</div>
    </div>
    <div class="hc-tier-card">
        <div class="hc-tier-label">Apostle Reserve</div>
        <div class="hc-tier-coins">12 ✦</div>
        <div class="hc-tier-desc">Twelve coins. One for each apostle. Yes, including that one.</div>
        <div class="hc-tier-price">€12.00</div>
    </div>
    <div class="hc-tier-card">
        <div class="hc-tier-label">Archangel Vault</div>
        <div class="hc-tier-coins">33 ✦</div>
        <div class="hc-tier-desc">Thirty-three coins. The holiest number. Do not ask why.</div>
        <div class="hc-tier-price">€33.33</div>
    </div>
    <div class="hc-tier-card" style="grid-column: 1 / -1; border-color: #3C3E4A; background: linear-gradient(135deg, #f0f0f0, #e8e8e4);">
        <div class="hc-tier-label">⚡ Omnipotent Edition</div>
        <div class="hc-tier-coins">ꝏ ✦</div>
        <div class="hc-tier-desc">Infinite coins. Conceptually. We cannot ship infinity but we will try.</div>
        <div class="hc-tier-price">€99.99</div>
    </div>
</div>

<div class="hc-form-section">
    <h3>Claim your certificate</h3>
    <p style="font: 12px/18px 'Inter', sans-serif; color: #666; margin-bottom: 16px;">
        A legally decorative, spiritually binding PDF certificate proving your HALO holdings.
        Accepted by 0 exchanges but spiritually recognised by at least several realms.
    </p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="halo-crypto.php" class="mb-4">
        <div class="mb-3">
            <label class="form-label">Your name <span style="color:#888; font-size:11px;">(for the celestial ledger)</span></label>
            <input type="text" name="name" class="form-control"
                value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                placeholder="Full mortal name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Coin tier</label>
            <select name="tier" class="form-select">
                <option value="sinner" <?php echo (($_POST['tier'] ?? '') === 'sinner')     ? 'selected' : ''; ?>>Sinner's Stack – 1 HALO – €2.99</option>
                <option value="disciple" <?php echo (($_POST['tier'] ?? 'disciple') === 'disciple') ? 'selected' : ''; ?>>Disciple Bundle – 7 HALO – €7.77</option>
                <option value="apostle" <?php echo (($_POST['tier'] ?? '') === 'apostle')    ? 'selected' : ''; ?>>Apostle Reserve – 12 HALO – €12.00</option>
                <option value="archangel" <?php echo (($_POST['tier'] ?? '') === 'archangel')  ? 'selected' : ''; ?>>Archangel Vault – 33 HALO – €33.33</option>
                <option value="omnipotent" <?php echo (($_POST['tier'] ?? '') === 'omnipotent') ? 'selected' : ''; ?>>Omnipotent Edition – ꝏ HALO – €99.99</option>
            </select>
        </div>

        <button type="submit" class="btn btn-dark btn-sm">Add to cart ✦</button>
    </form>
</div>

<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – HALO Coin';
$activePage = 'halo-crypto';
include __DIR__ . '/layout.php';
