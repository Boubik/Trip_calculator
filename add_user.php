<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator | Add user to item set</title>
</head>

<body>
    <?php
    include "template.php";
    include "functions.php";
    $conn = connect_db();
    session_start();
    if (!is_loged_in($conn, $_SESSION["username"], $_SESSION["password"])) {
        header("Location: index.php");
    }

    $navbarItems = '
    <li>
        <a href="view.php?id=' . filter_input(INPUT_GET, "id") . '&share=' . $_SESSION["username"] . '">Share</a>
    </li>
    <li>
        <a href="add_user.php?id=' . filter_input(INPUT_GET, "id") . '">Add user</a>
    </li>
    <li>
        <a href="add_item.php?id=' . filter_input(INPUT_GET, "id") . '">Add Item</a>                            
    </li>
    <li>
        <a href="logout.php">Logout</a>
    </li>
    ';
    ?>

    <section>
        <div class='container'>
            <?php
            if (isset($_POST["submit"])) {
                if (user_is_taken($conn, filter_input(INPUT_POST, "username"))) {
                    user_has_item_set($conn, filter_input(INPUT_GET, "id"), filter_input(INPUT_POST, "username"));
                    header("Location: view.php?id=" . filter_input(INPUT_GET, "id"));
                } else {
                    echo "User doesn't exist!";
                }
            }
            // echo "<form method=\"POST\" action=\"\">";
            // echo "<label for=\"fname\">New user to item set:</label>";
            // echo "<input type=\"text\" name=\"username\" placeholder=\"Username\" value=\"\">";
            // echo "<br>";
            // echo "<input type=\"submit\" name=\"submit\" value=\"Add\">";
            // echo "</form>";

            echo
            '
                <div class="center">
                <form method="POST" action="">
                <div class="txt_field">
                <input type="text" name="username" placeholder="Name of the user" value="">                             
                <span></span>
                <label>Add user</label>
                </div>
                <input type="submit" name="submit" value="Add">
                <div class="signup_link">
                </div>
                </form>
                </div>
                ';
            ?>
        </div>
    </section>
</body>

</html>