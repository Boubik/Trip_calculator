<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator | Register</title>
    <link rel="stylesheet" href="style/default.scss">
    <script src="js/sha3.js"></script>
    <script type="text/javascript">
        function changePasswords() {
            if (document.getElementById("password1").value.length >= 6 && document.getElementById("password1").value == document.getElementById("password2").value) {
                var hash1 = CryptoJS.SHA3(document.getElementById("password1").value, {
                    outputLength: 512
                });
                var hash2 = CryptoJS.SHA3(document.getElementById("password2").value, {
                    outputLength: 512
                });
                document.getElementById("password1").value = hash1;
                document.getElementById("password2").value = hash2;
            }
        }
    </script>
</head>

<body>
    <?php
    include "functions.php";
    $conn = connect_db();
    session_start();
    if (isset($_POST["username"]) and !is_null($_POST["username"])) {
        $user_is_taken = user_is_taken($conn, $_POST["username"]);
    }
    $password_is_not_same = false;
    $short_password = false;
    if (isset($_POST["password1"]) and !is_null($_POST["password1"])) {
        if (strcasecmp($_POST["password1"], $_POST["password2"])) {
            $password_is_not_same = true;
        }
        if (strlen($_POST["password1"]) < 6) {
            $short_password = true;
        }
        if (!$user_is_taken && !$password_is_not_same && !$short_password) {
            register($conn, $_POST["username"], $_POST["password1"]);
            $_SESSION["username"] = $_POST["username"];
            $_SESSION["password"] = $_POST["password1"];
            header("Location: index.php");
        }
    }
    ?>

    <header>
        <h1><a href="/">Split calculator</a></h1>
    </header>

    <br>

    <section>
        <?php
        if (isset($user_is_taken) and $user_is_taken) {
            echo "Username is already taken. ";
        }
        if (isset($short_password) and $short_password) {
            echo "Password need to be at least 6 characters. ";
        }
        if (isset($password_is_not_same) and $password_is_not_same) {
            echo "Passwords need to be same. ";
        }
        echo "<form onsubmit=\"changePasswords()\" method=\"POST\" action=\"\">";
        echo "<label for=\"fname\">Username:</label>";
        echo "<input type=\"text\" id=\"username\" name=\"username\" placeholder=\"Username\" value=\"\">";
        echo "<br>";
        echo "<label for=\"lname\">Password:</label>";
        echo "<input type=\"password\" id=\"password1\" name=\"password1\" placeholder=\"Password\">";
        echo "<br>";
        echo "<label for=\"lname\">Password:</label>";
        echo "<input type=\"password\" id=\"password2\" name=\"password2\" placeholder=\"Repeat password\">";
        echo "<br>";
        echo "<input type=\"submit\" name=\"submit\" value=\"Register\">";
        echo "</form>";
        ?>
    </section>
</body>

</html>