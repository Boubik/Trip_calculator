<!DOCTYPE html>
<html lang="en">

<head>
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Split calculator</title>
    <link rel="stylesheet" href="style/default.scss">
    <link rel="icon" type="image/svg" href="./images/calculator_favicon.svg">
</head>

<body>
    <?php
    include "functions.php";
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

    <main>
        <section class="navigation">
            <div class="nav-container">
                <div class="brand">
                    <img src="./images/icons8-calculator.svg" alt="">
                    <a href="#!">Split-Calculator</a>
                </div>

                <nav>
                    <div class="nav-mobile"><a id="navbar-toggle" href="#!"><span></span></a></div>
                    <ul class="nav-list">
                        <li>
                            <a href="#!">Home</a>
                        </li>
                        <li>
                            <a href="#!">About</a>
                        </li>
                        <li>
                            <a href="https://github.com/Boubik/Trip_calculator">GitHub</a>
                        </li>
                        <li>
                            <a href="#!">Contact</a>
                        </li>
                        <?php
                        if ($login) {
                            echo '
                        <li>
                            <a href="add_itemset.php">Add item</a>
                        </li>
                        <li>
                            <a href="logout.php">Logout</a>
                        </li>
                        ';
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </section>
        <section>
            <?php
            if ($login) {
                $sql = "SELECT `id`, `name` FROM `item_set` INNER JOIN `user_has_item_set` ON `user_has_item_set`.`item_set_id` = `item_set`.`id` WHERE`user_has_item_set`.`user_name` = '" . $_SESSION["username"] . "'";
                $rows = select($conn, $sql);

                foreach ($rows as $row) {
                    echo "<div id='grid'><a id='item' href=view.php?id=" . str_replace(' ', '%20', $row["id"]) . ">" . $row["name"] . "</a></div>";
                }
            } else {
                echo
                '
                    <div class="center">
                        <h1>Login</h1>
                        
                        <form onsubmit="changePasswords()" method="POST" action="">
                        
                            <div class="txt_field">
                                <input type="text" id="username" name="username" placeholder="Username" value="">                                
                                <span></span>
                                <label>Username</label>
                            </div>

                            <div class="txt_field">
                                <input type="password" id="password" name="password" placeholder="Password">
                                <span></span>
                                <label>Password</label>
                            </div>

                            <input type="submit" name="submit" value="Login">
                            <div class="signup_link">
                            Not a member? <a href="register.php"> Sign-Up</a>
                            </div>

                        </form>

                    </div>
                ';
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
<script src="./js/navbar.js"></script>


</html>