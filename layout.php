<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Hex & Halo Shop'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Fonts + icons -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />

    <link rel="stylesheet" href="styles/angelic-shop.css">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">
</head>

<body>

    <div class="dc-body">
        <div class="dc-body2 container-fluid">
            <div class="row g-0 dc-row">

                <!-- LEFT PANEL (brand) -->
                <div class="col-12 col-lg-4 position-relative dc-left-col">
                    <div class="dc-char-color"></div>

                    <div class="dc-char-left">
                        <!-- <a href="index.php" class="dc-char-return d-none d-md-block">
                            <div></div>
                            return to<br>the directory
                        </a> -->
                        <div class="dc-char-flower d-none d-md-block"></div>

                        <div class="dc-char-icon mx-auto mx-lg-0">
                            <div class="dc-char-icon2"></div>
                        </div>

                        <h2 style="text-align: center !important;">Halo &amp; Hex</h2>

                        <div class="dc-char-start">
                            <div>charms</div>
                            <div>ritual kits</div>
                            <div>readings</div>
                            <div>blessings</div>
                        </div>

                        <p>
                            Light-hearted magic for mortals. A little curse relief, a little halo glow.
                        </p>
                    </div>
                </div>

                <!-- RIGHT PANEL (nav + page content) -->
                <div class="col-12 col-lg-8 dc-right-col">
                    <div class="dc-char-right">
                        <div class="dc-char-menu">
                            <a href="index.php"
                                class="dc-menu-link <?php echo $activePage === 'shop' ? 'dc-menu-link-active' : ''; ?>">
                                intro
                            </a>
                            <a href="cart.php"
                                class="dc-menu-link <?php echo $activePage === 'cart' ? 'dc-menu-link-active' : ''; ?>">
                                cart
                            </a>
                            <a href="certificate.php"
                                class="dc-menu-link <?php echo $activePage === 'certificate' ? 'dc-menu-link-active' : ''; ?>">
                                certificate
                            </a>

                            <a href="halo-crypto.php"
                                class="dc-menu-link <?php echo $activePage === 'halo-crypto' ? 'dc-menu-link-active' : ''; ?>">
                                halo crypto
                            </a>

                            <a href="about.php"
                                class="dc-menu-link <?php echo $activePage === 'about' ? 'dc-menu-link-active' : ''; ?>">
                                about
                            </a>
                        </div>

                        <div class="dc-char-bulk">
                            <div class="dc-char-bulk2">
                                <?php echo $content; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php
    // optional: if you still want a footer component
    // include __DIR__ . '/../components/footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Split heading first word into big line
        $('.dc-char-left h2').each(function() {
            var me = $(this),
                t = me.text().split(' ');
            me.html('<div>' + t.shift() + '</div> ' + t.join(' '));
        });
    </script>
</body>

</html>