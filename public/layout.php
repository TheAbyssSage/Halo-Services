<?php
// public/layout.php
// expects: $pageTitle (string), $content (string HTML)
?>
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
</head>

<body>

    <div class="dc-body">
        <div class="dc-body2 container-fluid">
            <div class="row g-0 dc-row">

                <!-- LEFT PANEL -->
                <div class="col-12 col-lg-4 position-relative dc-left-col">
                    <div class="dc-char-color"></div>

                    <div class="dc-char-left">
                        <a href="index.php" class="dc-char-return d-none d-md-block">
                            <div></div>
                            return to<br>the directory
                        </a>
                        <div class="dc-char-flower d-none d-md-block"></div>

                        <div class="dc-char-icon mx-auto mx-lg-0">
                            <div class="dc-char-icon2"></div>
                        </div>

                        <h2>Halo &amp; Hex</h2>

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

                <!-- RIGHT PANEL -->
                <div class="col-12 col-lg-8 dc-right-col">
                    <div class="dc-char-right">
                        <div class="dc-char-menu">
                            <a href="index.php" class="dc-menu-link">shop</a>
                            <a href="cart.php" class="dc-menu-link">cart</a>
                            <a href="#" class="dc-menu-link">charms</a>
                            <a href="#" class="dc-menu-link">rituals</a>
                            <a href="#" class="dc-menu-link">about</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Split heading first word into big line (works on all screen sizes)
        $('.dc-char-left h2').each(function() {
            var me = $(this),
                t = me.text().split(' ');
            me.html('<div>' + t.shift() + '</div> ' + t.join(' '));
        });
    </script>
</body>

</html>