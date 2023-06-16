<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg" href="./images/calculator_favicon.svg">
    <title><?php echo isset($pageTitle) ? $pageTitle : "Split Calculator"; ?></title>
    <script src="./js/navbar.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style/default.css">
</head>

<body>
    <header>
        <section class="navigation">
            <div class="nav-container">
                <div class="brand">
                    <img src="./images/icons8-calculator.svg" alt="">
                    <a href="index.php">Split-Calculator</a>
                </div>

                <nav>
                    <div class="nav-mobile"><a id="navbar-toggle" href="#!"><span></span></a></div>
                    <ul class="nav-list">
                        <li>
                            <a href="index.php">Trips</a>
                        </li>
                        <li>
                            <a href="https://github.com/Boubik/Trip_calculator">GitHub</a>
                        </li>
                        <li>
                            <?php echo isset($navbarItems) ? $navbarItems : ''; ?>
                        </li>
                        <li>
                            <a href="logout.php">Logout</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </section>
    </header>

</body>

</html>