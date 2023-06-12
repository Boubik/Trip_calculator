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

    <header>
        <h1><a href="index.php">Split calculator</a></h1>
    </header>

    <br>

    <section>
        <div class='container'>
            <?php
            if (isset($_POST["submit"])) {
                if (user_is_taken($conn, filter_input(INPUT_POST, "username"))) {
                    user_has_item_set($conn, filter_input(INPUT_GET, "id"), filter_input(INPUT_POST, "username"));
                    header("Location: view.php?id=" . filter_input(INPUT_GET, "id"));
                } else {
                    echo "User doesn't exist";
                }
            }
            echo "<form method=\"POST\" action=\"\">";
            echo "<label for=\"fname\">New user to item set:</label>";
            echo "<input type=\"text\" name=\"username\" placeholder=\"Username\" value=\"\">";
            echo "<br>";
            echo "<input type=\"submit\" name=\"submit\" value=\"Add\">";
            echo "</form>";
            ?>
        </div>
    </section>
</body>

</html>