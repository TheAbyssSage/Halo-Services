<?php
// public/about.php
session_start();

ob_start();
?>
<!-- TODO: your about content -->
<h1>About Hex &amp; Halo</h1>
<p>Who we are, why the halos, and what the hex is going on.</p>
<?php
$content    = ob_get_clean();
$pageTitle  = 'Hex & Halo – About';
$activePage = 'about';

include __DIR__ . '/layout.php';
