<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// No products, no add-to-cart handling – this is now an intro / landing page.

// Build page content
ob_start();
?>
<div class="cert-hero">
    <div class="cert-hero-icon">✦</div>
    <h1>Welcome to <span>Hex &amp; Halo</span></h1>
    <p class="cert-hero-sub">
        A tiny celestial bureau for mortals who want their blessings, chaos, and tiny miracles
        documented in lovingly over‑engineered PDF form.
    </p>
    <div class="cert-hero-meta">
        <div class="cert-pill">angelic paperwork</div>
        <div class="cert-pill">soft chaos engineering</div>
        <div class="cert-pill">spiritually binding, legally decorative</div>
    </div>
</div>

<div class="cert-lore">
    <h2>What we actually do</h2>
    <p>
        Hex &amp; Halo lives somewhere between a magic shop, a government office, and a running joke
        that escaped the group chat. Instead of selling potions or potpourri, we issue
        <strong>certificates</strong>: blessing infusions, angelic wards, chaos licenses, and
        miracle passes – all generated on demand, personalised with your name, dates, QR codes,
        and far too much lore.
    </p>
    <p>
        Every document is part satire, part world‑building, and part love letter to anyone who
        enjoys dramatic paperwork about very small things.
    </p>
</div>

<div class="cert-lore">
    <h2>How it works (in mortal terms)</h2>
    <p>
        You wander through the sections in the navigation above:
        the <em>Certificate</em> bureau, the experimental <em>Halo Crypto</em> corner,
        and whatever else the angels haven’t shut down yet.
        You tell us your name, pick a tier, and we mint a bespoke PDF –
        stamped with IDs, dates, and fine print that sounds like it came from a very
        dramatic celestial compliance department.
    </p>
    <p>
        Behind the scenes it’s just classic PHP, Composer packages, Stripe sessions,
        and Dompdf doing their thing. The magic is fictional. The code is not.
    </p>
</div>

<div class="cert-lore">
    <h2>What you&apos;ll find in the wings</h2>
    <p>
        In the <strong>Certificates</strong> section, you&apos;ll meet:
    </p>
    <ul style="margin: 6px 0 8px 18px; padding:0; font-size:13px; line-height:1.6; color:#444;">
        <li><strong>Weekly Blessing Infusion</strong> – a week of softer dice rolls in your favour.</li>
        <li><strong>Angelic Protection Seal</strong> – one‑month wards for everything from bad vibes to boss fights.</li>
        <li><strong>Chaos Containment License</strong> – official permission to be a gremlin, but with paperwork.</li>
        <li><strong>Minor Miracles Pass</strong> – a punch‑card of small, cinematic coincidences.</li>
    </ul>
    <p>
        The <strong>Halo Crypto</strong> page explores what happens when you treat a fake holy token
        like it has a real on‑chain bureaucracy. It&apos;s all in good fun, and absolutely not
        financial advice – especially not divine financial advice.
    </p>
</div>

<div class="cert-lore">
    <h2>Who this place is for</h2>
    <p>
        Hex &amp; Halo is for people who collect inside jokes, love world‑building, and think
        “biblically accurate angel” is a valid design direction. If you&apos;ve ever wanted a
        certificate that says you are officially blessed, mildly protected, or licensed to
        unleash contained chaos, you&apos;re in the right branch of the multiverse.
    </p>
    <p>
        When you&apos;re ready, head to the <strong>Certificate</strong> section to mint your first
        piece of celestial paperwork, or to <strong>Halo Crypto</strong> to join the holiest,
        silliest token experiment this side of the upper atmosphere.
    </p>
</div>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Welcome';
$activePage = 'shop'; // keeps the "shop"/home tab highlighted in the nav

include __DIR__ . '/layout.php';
