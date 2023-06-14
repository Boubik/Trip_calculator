<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator | Add user to item set</title>
    <link rel="stylesheet" href="style/default.scss">
</head>

<body>
    <?php
    include "functions.php";
    $conn = connect_db();
    session_start();
    if (!is_loged_in($conn, $_SESSION["username"], $_SESSION["password"])) {
        header("Location: index.php");
    }
    ?>

    <section>
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
                            <a href="index.php">Home</a>
                        </li>
                        <li>
                            <a href="index.php">About</a>
                        </li>
                        <li>
                            <a href="https://github.com/Boubik/Trip_calculator">GitHub</a>
                        </li>
                        <li>
                            <a href="index.php">Contact</a>
                        </li>
                        <li>
                            <a href="add_itemset.php">Add item</a>
                        </li>
                        <li>
                            <a href="logout.php">Logout</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </section>

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