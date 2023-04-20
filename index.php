<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator</title>
    <link rel="stylesheet" href="style/default.css">
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
        header("Location: /");
    } else {
    }

    if (isset($_SESSION["username"]) or isset($_SESSION["password"])) {
        $login = is_loged_in($conn, $_SESSION["username"], $_SESSION["password"]);
    } else {
        $login = false;
    }
    ?>

    <header>
        <h1><a href="/">Split calculator</a></h1>
        <?php
        if ($login) {
            echo "<a href=\"add_itemset.php\">Add new item set</a>";
            echo "<a href=\"logout.php\">Logout of " . $_SESSION["username"] . "</a>";
        } else {
            echo "<a href=\"register.php\">Register</a>";
        }
        ?>
    </header>

    <br>

    <section>
        <div class='container'>
            <?php
            if ($login) {
                $sql = "SELECT `id`, `name` FROM `item_set` INNER JOIN `user_has_item_set` ON `user_has_item_set`.`item_set_id` = `item_set`.`id` WHERE`user_has_item_set`.`user_name` = '" . $_SESSION["username"] . "'";
                $rows = select($conn, $sql);

                foreach ($rows as $row) {
                    echo "<div id='grid'><a id='item' href=view.php?id=" . str_replace(' ', '%20', $row["id"]) . ">" . $row["name"] . "</a></div>";
                }
            } else {
                echo "<form method=\"POST\" action=\"\">";
                echo "<label for=\"fname\">Username:</label>";
                echo "<input type=\"text\" name=\"username\" placeholder=\"Username\" value=\"\">";
                echo "<br>";
                echo "<label for=\"lname\">Password:</label>";
                echo "<input type=\"password\" name=\"password\" placeholder=\"Password\">";
                echo "<br>";
                echo "<input type=\"submit\" name=\"submit\" value=\"Login\">";
                echo "</form>";
            }





            ?>
        </div>
    </section>
</body>

</html>