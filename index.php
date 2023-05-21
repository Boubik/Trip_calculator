<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Split calculator</title>
    <link rel="stylesheet" href="style/default.css">

    <script src="js/sha3.js"></script>
    <script type="text/javascript">
        function changePasswords() {
            if (document.getElementById("password").value.length != 0) {
                var hash = CryptoJS.SHA3(document.getElementById("password").value, {
                    outputLength: 512
                });
                document.getElementById("password").value = hash;
            }
        }
    </script>
</head>

<body>
    <?php
    include "functions.php";
    include "bootstrap.php";
    $conn = connect_db();
    session_start();

    if ((isset($_POST["username"]) and !is_null($_POST["username"])) and (isset($_POST["username"]) and !is_null($_POST["password"]))) {
        $_SESSION["username"] = $_POST["username"];
        $_SESSION["password"] = $_POST["password"];
        unset($_POST["username"]);
        unset($_POST["password"]);
        header("Location: index.php");
    } else {
    }

    if (isset($_SESSION["username"]) or isset($_SESSION["password"])) {
        $login = is_loged_in($conn, $_SESSION["username"], $_SESSION["password"]);
    } else {
        $login = false;
    }
    ?>

    <header>
        <nav class="navbar navbar-expand-lg bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Navbar</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Link</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Dropdown
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Action</a></li>
                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled">Disabled</a>
                        </li>
                    </ul>
                    <form class="d-flex" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-success" type="submit">Search</button>
                    </form>
                </div>
            </div>
        </nav>

        <?php
        if ($login) {
            echo "<a href=\"add_itemset.php\">Add new item set</a>";
            echo "<a href=\"logout.php\">Logout of " . $_SESSION["username"] . "</a>";
        }
        ?>
    </header>

    <main>
        <section>
            <?php
            if ($login) {
                $sql = "SELECT `id`, `name` FROM `item_set` INNER JOIN `user_has_item_set` ON `user_has_item_set`.`item_set_id` = `item_set`.`id` WHERE`user_has_item_set`.`user_name` = '" . $_SESSION["username"] . "'";
                $rows = select($conn, $sql);

                foreach ($rows as $row) {
                    echo "<div id='grid'><a id='item' href=view.php?id=" . str_replace(' ', '%20', $row["id"]) . ">" . $row["name"] . "</a></div>";
                }
            } else {
                echo "<div class=\"login-wrap\">
                        <h2>Login</h2>

                        <form onsubmit=\"changePasswords()\" method=\"POST\" action=\"\">
                            <input type=\"text\" id=\"username\" name=\"username\" placeholder=\"Username\" value=\"\">
                            <input type=\"password\" id=\"password\" name=\"password\" placeholder=\"Password\">
                            <button type=\"submit\" name=\"submit\" value=\"Login\">Sign In</button>
                            <a href=\"register.php\"><p> Don't have an account? Register! </p></a>
                        </form>

                      </div>";
            }
            ?>
        </section>
    </main>
</body>
<script type="text/javascript">
    $('.form-signin').submit(function() {
        if ($("#password").val().length !== 0) {
            var hash = CryptoJS.SHA3($("#password").val(), {
                outputLength: 512
            });
            $("#password").val(hash);
        }
    });
</script>

</html>