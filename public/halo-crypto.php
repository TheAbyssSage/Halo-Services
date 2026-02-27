<?php
// public/halo-crypto.php
session_start();

ob_start();
?>
<!-- TODO: your halo-crypto content goes here -->
<h1>Halo Crypto</h1>
<p>Angelic discounts and cursed exchange rates.</p>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Halo Crypto';
$activePage = 'halo-crypto'; // you'll match this in layout.php

include __DIR__ . '/layout.php';
