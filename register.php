<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator | Register</title>
    <link rel="stylesheet" href="style/default.css">
</head>

<body>
    <?php
    include "functions.php";
    $conn = connect_db();
    session_start();
    if(!is_null($_POST["username"])){
        $user_is_taken = user_is_taken($conn, $_POST["username"]);
    }
    $password_is_not_same = false;
    $short_password = false;
    if(!is_null($_POST["password1"])){
        if(strcasecmp($_POST["password1"], $_POST["password2"])){
            $password_is_not_same = true;
        }
        if(strlen($_POST["password1"]) < 6){
            $short_password = true;
        }
        if(!$user_is_taken && !$password_is_not_same && !$short_password){
            register($conn, $_POST["username"], $_POST["password1"]);
            $_SESSION["username"] = $_POST["username"];
            $_SESSION["password"] = $_POST["password1"];
            header("Location: /");
        }
    }
    ?>

    <header>
        <h1><a href="/">Split calculator</a></h1>
    </header>

    <br>

    <section>
        <div class='container'>
            <?php
            if($user_is_taken){
                echo "Username is already taken. ";
            }
            if($short_password){
                echo "Password need to be at least 6 characters. ";
            }
            if($password_is_not_same){
                echo "Passwords need to be same. ";
            }
            echo "<form method=\"POST\" action=\"\">";
            echo "<label for=\"fname\">Username:</label>";
            echo "<input type=\"text\" name=\"username\" placeholder=\"Username\" value=\"\">";
            echo "<br>";
            echo "<label for=\"lname\">Password:</label>";
            echo "<input type=\"password\" name=\"password1\" placeholder=\"Password\">";
            echo "<br>";
            echo "<label for=\"lname\">Password:</label>";
            echo "<input type=\"password\" name=\"password2\" placeholder=\"Repeat password\">";
            echo "<br>";
            echo "<input type=\"submit\" name=\"submit\" value=\"Register\">";
            echo "</form>";
            ?>
        </div>
    </section>
</body>

</html>