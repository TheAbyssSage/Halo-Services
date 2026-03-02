<?php
// public/certificate.php
session_start();

ob_start();
?>
<div class="cert-hero">
    <div class="cert-hero-icon">✦</div>
    <h1>Celestial <span>Certificates</span></h1>
    <p class="cert-hero-sub">
        Official Hex &amp; Halo paperwork for mortals who want their nonsense documented in tasteful,
        high‑resolution PDF form. All spiritually binding, none legally enforceable.
    </p>
    <div class="cert-hero-meta">
        <div class="cert-pill">instant download</div>
        <div class="cert-pill">qr‑verified lore</div>
        <div class="cert-pill">angel‑adjacent branding</div>
    </div>
</div>

<div class="cert-lore">
    <h2>Pick your flavor of divine bureaucracy</h2>
    <p>
        Every certificate here spins up its own bespoke, lore‑heavy document: protection seals,
        chaos licenses, miracle passes and week‑long blessings. Each one is generated just for you,
        stamped with IDs, dates, and dramatic language about entities with too many wings.
    </p>
    <p>
        Start by choosing which branch of the Bureau you’d like to annoy: gentle miracles,
        angelic security, regulated chaos, or controlled reality glitches.
    </p>
</div>

<div class="shop-grid">
    <!-- Blessing -->
    <div class="shop-card">
        <div class="shop-card-header">
            <span class="shop-card-pill">7‑day ward</span>
            <h2>Weekly Blessing Infusion</h2>
        </div>
        <p class="shop-card-desc">
            A week of biased dice rolls in your favour. Softens cursed moments and boosts quiet wins.
        </p>
        <div class="shop-card-meta">
            <div class="shop-card-price">€ 4.99</div>
            <a href="blessing.php" class="btn btn-dark btn-sm">Summon</a>
        </div>
    </div>

    <!-- Angelic Protection -->
    <div class="shop-card">
        <div class="shop-card-header">
            <span class="shop-card-pill">1‑month ward</span>
            <h2>Angelic Protection Seal</h2>
        </div>
        <p class="shop-card-desc">
            One month of biblically accurate guardians on quiet duty: from light ward to exam‑week priority.
        </p>
        <div class="shop-card-meta">
            <div class="shop-card-price">Tiered ward</div>
            <a href="protection.php" class="btn btn-dark btn-sm">Summon</a>
        </div>
    </div>

    <!-- Chaos License -->
    <div class="shop-card">
        <div class="shop-card-header">
            <span class="shop-card-pill">hex control</span>
            <h2>Chaos Containment License</h2>
        </div>
        <p class="shop-card-desc">
            Official permission to deploy contained chaos, audited by something with feathers and a clipboard.
        </p>
        <div class="shop-card-meta">
            <div class="shop-card-price">For responsible gremlins only</div>
            <a href="chaos-license.php" class="btn btn-dark btn-sm">Summon</a>
        </div>
    </div>

    <!-- Minor Miracles -->
    <div class="shop-card">
        <div class="shop-card-header">
            <span class="shop-card-pill">tiny miracles</span>
            <h2>Minor Miracles Pass</h2>
        </div>
        <p class="shop-card-desc">
            A punch‑card of small, cinematic reality edits. Good for vibes, not for taxes.
        </p>
        <div class="shop-card-meta">
            <div class="shop-card-price">Tiered miracles</div>
            <a href="minor-miracles.php" class="btn btn-dark btn-sm">Summon</a>
        </div>
    </div>
</div>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Certificates';
$activePage = 'certificate';

include __DIR__ . '/layout.php';
