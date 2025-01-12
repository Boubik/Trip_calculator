<?php
include "functions.php";
$conn = connect_db();
session_start();
if (!is_loged_in($conn, $_SESSION["username"], $_SESSION["password"])) {
    header("Location: index.php");
}
$pageTitle = "Split Calculator | View";
$navbarItems = '
<li>
    <a href="logout.php">Logout</a>
</li>
';
$head = '<script src="js/sha3.js"></script>
<script src="js/changePasswords.js"></script>';
include "template.php";
?>

<main>

    <div class="container">
        <div class='center'>
            <?php
            if (isset($_POST["submit"])) {
                $oldpassword_is_not_same = false;
                $password_is_not_same = false;
                $short_password = false;
                if (strcasecmp($_SESSION["password"], $_POST["oldPassword"])) {
                    $oldpassword_is_not_same = true;
                }
                if (strcasecmp($_POST["newPassword"], $_POST["newPassword2"])) {
                    $password_is_not_same = true;
                }
                if (strlen($_POST["newPassword"]) < 6) {
                    $short_password = true;
                }
                print_r($_POST);
                if (!$oldpassword_is_not_same && !$password_is_not_same && !$short_password) {
                    change_password($conn, $_SESSION["username"], $_POST["oldPassword"], $_POST["newPassword"]);
                    $_SESSION = null;
                    header("Location: index.php");
                }
            }

            echo
            '
            <form onsubmit="changePasswords()" method="POST" action="">
            <div class="txt_field">
            <input type="password" id="oldPassword" name="oldPassword" value="">
            <span></span>
            <label for="fname">Old password</label>
            </div>
            <div class="txt_field">
            <input type="password" id="newPassword" name="newPassword" value="">
            <span></span>
            <label for="fname">New password</label>
            </div>
            <div class="txt_field">
            <input type="password" id="newPassword2" name="newPassword2" value="">
            <span></span>
            <label for="fname">New password confirmation</label>
            </div>
            <input type="submit" name="submit" value="Logout and change Password">
            </form>
            </div>
            ';

            if (isset($oldpassword_is_not_same) and $oldpassword_is_not_same) {
                echo '<script>alert("Old password need to be same as the current one.")</script>';
            }
            if (isset($short_password) and $short_password) {
                echo '<script>alert("Password need to be at least 6 characters.")</script>';
            }
            if (isset($password_is_not_same) and $password_is_not_same) {
                echo '<script>alert("Passwords need to be same.")</script>';
            }
            ?>
        </div>
    </div>
</main>
</body>

</html>