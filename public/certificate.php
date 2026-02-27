<?php
// public/certificate.php
session_start();

ob_start();
?>
<!-- TODO: certificate preview / download logic -->
<h1>Blessing Certificate</h1>
<p>Printable proof that the angels are, in fact, on your side.</p>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – Certificate';
$activePage = 'certificate';

include __DIR__ . '/layout.php';
