<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg" href="./images/calculator_favicon.svg">
    <title><?php echo isset($pageTitle) ? e($pageTitle) : 'Split Calculator'; ?></title>
    <link rel="stylesheet" href="style/default.css?v=20260409f">
    <?php echo isset($head) ? $head : ''; ?>
</head>

<body>
    <header>
        <section class="navigation">
            <div class="nav-container">
                <div class="brand">
                    <a href="index.php">
                        <img src="./images/icons8-calculator.svg" alt="">
                        Split-Calculator
                    </a>
                </div>

                <nav>
                    <div class="nav-mobile"><a id="navbar-toggle" href="#!"><span></span></a></div>
                    <ul class="nav-list">
                        <li>
                            <a href="index.php">Home</a>
                        </li>
                        <?php echo isset($navbarItems) ? $navbarItems : ''; ?>
                    </ul>
                </nav>
            </div>
        </section>
    </header>
    <script src="./js/navbar.js"></script>
