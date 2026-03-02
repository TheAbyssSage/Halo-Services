<?php
// public/about.php
session_start();

ob_start();
?>
<div class="cert-hero">
    <div class="cert-hero-icon">👁️‍🗨️</div>
    <h1>About <span>Hex &amp; Halo</span></h1>
    <p class="cert-hero-sub">
        A tiny, unofficial department of celestial bureaucracy, specialising in paperwork for
        mortals who like their magic documented, timestamped, and mildly ridiculous.
    </p>
    <div class="cert-hero-meta">
        <div class="cert-pill">angelic admin</div>
        <div class="cert-pill">soft chaos engineering</div>
        <div class="cert-pill">legally decorative</div>
    </div>
</div>

<div class="cert-lore">
    <h2>What is even going on here?</h2>
    <p>
        Hex &amp; Halo started with a simple thought: “What if the universe had a front desk?”
        Not the terrifying cosmic one – just a smaller branch where you could file a request
        for a week of good luck, a handful of minor miracles, or an official license to be
        a chaos gremlin <em>responsibly</em>.
    </p>
    <p>
        This shop is that front desk. Every product is basically a joke that grew opposable
        thumbs, learned PHP, and started generating PDFs with far too much lore, QR codes,
        and bureaucratic flavour text.
    </p>
</div>

<div class="cert-lore">
    <h2>Design philosophy (if you can call it that)</h2>
    <p>
        The aesthetic lives somewhere between Renaissance angel painting, glitch art,
        and the feeling of opening a mysterious file named <code>DO_NOT_OPEN_FINAL_v7.pdf</code>.
        We like glowing halos, radial gradients, and typography that looks like a slightly
        over‑eager Ministry of Magic intern made it in Figma.
    </p>
    <p>
        Under the jokes, the code actually takes itself seriously: real payment handling,
        real session logic, real PDF generation. The magic is completely fake; the engineering
        is not.
    </p>
</div>

<div class="cert-lore">
    <h2>Who is this for?</h2>
    <p>
        People who:
    </p>
    <ul style="margin: 6px 0 8px 18px; padding:0; font-size:13px; line-height:1.6; color:#444;">
        <li>collect inside jokes like trading cards,</li>
        <li>enjoy dramatic documentation of very small things,</li>
        <li>think “biblically accurate angel” is a valid design direction,</li>
        <li>want receipts for their chaos, blessings, and tiny miracles.</li>
    </ul>
    <p>
        Whether you’re buying something for yourself, a friend, or that one coworker who
        keeps almost starting boss fights with their own to‑do list, there’s probably a
        certificate here with their name on it.
    </p>
</div>

<div class="cert-lore">
    <h2>Disclaimers, obviously</h2>
    <p>
        Hex &amp; Halo does not guarantee salvation, perfect exam scores, or that your crush
        will suddenly “just get it”. We <em>do</em> guarantee lovingly over‑written PDFs,
        delightfully specific fine print, and the comforting feeling that somewhere out there,
        a many‑eyed angel is quietly rooting for you.
    </p>
    <p>
        In other words: not financial advice, not spiritual doctrine, just really committed
        celestial stationery.
    </p>
</div>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – About';
$activePage = 'about';

include __DIR__ . '/layout.php';
