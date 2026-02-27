<?php
// public/layout.php
// $pageTitle (string), $content (string HTML) expected

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Hex & Halo Shop'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Fonts + icons from CodePen -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />

    <link rel="stylesheet" href="styles/angelic-shop.css">
</head>

<body>

    <div class="dc-body">
        <div class="dc-body2">
            <div class="dc-char">
                <div class="dc-char-color"></div>

                <!-- LEFT PANEL: brand + tagline -->
                <div class="dc-char-left">
                    <a href="index.php" class="dc-char-return">
                        <div></div>
                        return to<br>the directory
                    </a>
                    <div class="dc-char-flower"></div>
                    <div class="dc-char-icon">
                        <div class="dc-char-icon2"></div>
                    </div>
                    <h2>Halo</h2>
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

                <!-- RIGHT PANEL: page-specific content -->
                <div class="dc-char-right">
                    <div class="dc-char-menu">
                        <a href="index.php">shop</a>
                        <a href="cart.php">cart</a>
                        <a href="#">charms</a>
                        <a href="#">rituals</a>
                        <a href="about.php">about</a>
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

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // CodePen JS: split heading first word into big line
        $('.dc-char-left h2').each(function() {
            var me = $(this),
                t = me.text().split(' ');
            me.html('<div>' + t.shift() + '</div> ' + t.join(' '));
        });
    </script>
</body>

</html>