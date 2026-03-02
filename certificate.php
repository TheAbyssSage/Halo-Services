<?php
// public/certificate.php
session_start();

ob_start();
?>
<h1>Celestial Certificates</h1>
<p class="mb-3">
    Choose your flavor of official Hex &amp; Halo paperwork.
</p>

<div class="shop-grid">
    <!-- Blessing -->
    <div class="shop-card">
        <div class="shop-card-header">
            <span class="shop-card-pill">7-day ward</span>
            <h2>Weekly Blessing Infusion</h2>
        </div>
        <p class="shop-card-desc">
            Blessing-infused PDF that wards off minor evil for one mortal week.
        </p>
        <div class="shop-card-meta">
            <div class="shop-card-price">Free (for good souls)</div>
            <a href="blessing.php" class="btn btn-dark btn-sm">Summon</a>
        </div>
    </div>

    <!-- Angelic Protection -->
    <div class="shop-card">
        <div class="shop-card-header">
            <span class="shop-card-pill">1-month ward</span>
            <h2>Angelic Protection Seal</h2>
        </div>
        <p class="shop-card-desc">
            One-month angelic protection, with tiers from light ward to archangel priority.
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
            License to wield small, contained chaos under Halo Bureau supervision.
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
            Certifies you’re entitled to a reasonable number of minor miracles.
        </p>
        <div class="shop-card-meta">
            <div class="shop-card-price">Subject to availability</div>
            <a href="minor-miracles.php" class="btn btn-dark btn-sm">Summon</a>
        </div>
    </div>
</div>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Certificates';
$activePage = 'certificate';

include __DIR__ . '/layout.php';
