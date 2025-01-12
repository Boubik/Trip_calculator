<?php
$pageTitle = "Split Calculator | Trips";
$head = '<script src="js/sha3.js"></script>
<script src="js/changePasswordsIndex.js"></script>';

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

if (isset($_SESSION["username"]) and isset($_SESSION["password"])) {
    $login = is_loged_in($conn, $_SESSION["username"], $_SESSION["password"]);
} else {
    $login = false;
}
?>

<?php
if ($login) {
    $navbarItems = '
    <li>
        <a href="add_itemset.php">Add Trip</a>
    </li>
    <li>
        <a href="edit_account.php">Edit Account</a>
    </li>
    <li>
        <a href="logout.php">Logout</a>
    </li>
    ';
}
include "template.php";
?>


<main>
    <?php
    if ($login) {
        $sql = "SELECT `id`, `name` FROM `item_set` INNER JOIN `user_has_item_set` ON `user_has_item_set`.`item_set_id` = `item_set`.`id` WHERE`user_has_item_set`.`user_name` = '" . $_SESSION["username"] . "'";
        $rows = select($conn, $sql);

        echo
        '
            <h1 class="heading">TRIPS</h1>
            <div class="trips-vypis">
        ';
        if (count($rows) == 0) {
            echo '<h2>No trips add one with:</h2>';
            echo '<a class="goodButton" href="add_itemset.php">Add Trip</a>';
        } else {
            foreach ($rows as $row) {
                echo ' 
                        <a class="child" id="item" href="view.php?id=' . str_replace(" ", "%20", $row["id"]) . '">' . $row["name"] . '</a>
                    ';
            }
        }


        echo
        '   
                    </ol>
                </div>
            </div>
        ';
    }
    ////
    else {
        echo
        '
        <div class="container">
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
        </div>
        ';
    }
    ?>
</main>
</body>

</html>