<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator | Register</title>
    <script src="./js/changePasswordRegister.js"></script>
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
    include "template.php";
    ?>

    <?php
    if (isset($user_is_taken) and $user_is_taken) {
        echo '<script>alert("Username is already taken.")</script>';
    }
    if (isset($short_password) and $short_password) {
        echo '<script>alert("Password need to be at least 6 characters.")</script>';
    }
    if (isset($password_is_not_same) and $password_is_not_same) {
        echo '<script>alert("Passwords need to be same.")</script>';
    }
    echo '
    <div class="container">
            <div class="center">
                <h1>Register</h1>
                <form onsubmit="changePasswords()" method="POST" action="">
                
                    <div class="txt_field">
                        <input type="text" id="username" name="username" placeholder="Username" value="">                                
                        <span></span>
                        <label>Username</label>
                    </div>

                    <div class="txt_field">
                        <input type="password" id="password1" name="password1" placeholder="Password">
                        <span></span>
                        <label>Password</label>
                    </div>
                    <div class="txt_field">
                        <input type="password" id="password2" name="password2" placeholder="Repeat password">
                        <span></span>
                        <label>Password</label>
                    </div>

                    <input type="submit" name="submit" value="Register"></input>
                    <div class="signup_link">
                    Already a member? <a href="index.php"> Login</a>
                    </div>
                </form>
            </div>
        </div>';
    ?>
</body>

<script>
    function myalert_username() {
        alert("Username is already taken.");
    }

    function myalert_password_6ch() {
        alert("Password need to be at least 6 characters.");
    }

    function myalert_password_same() {
        alert("Passwords need to be same.");
    }
</script>

</html>
