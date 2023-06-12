<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator | Add users to item</title>
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
                print_r($_POST["users"]);
                add_users_to_item($conn, filter_input(INPUT_GET, "item_id"), $_POST["users"]);
                header("Location: view.php?id=" . filter_input(INPUT_GET, "id"));
            }

            echo "The people who will pay it";

            echo "<form method=\"POST\" action=\"\">";
            foreach (get_posible_payers($conn, filter_input(INPUT_GET, "id")) as $row) {
                if ($row["name"] == $_SESSION["username"]) {
                    echo "<input type=\"checkbox\" name=\"users[]\" value=\"" . $row["name"] . "\" checked>";
                } else {
                    echo "<input type=\"checkbox\" name=\"users[]\" value=\"" . $row["name"] . "\">";
                }
                //echo "<input type=\"checkbox\" name=\"users[]\" value=\"". $row["name"] ."\">";
                echo "<label for=\"fname\"> " . $row["name"] . "</label>";
                echo "<br>";
            }

            echo "<input type=\"submit\" name=\"submit\" value=\"Add\">";
            echo "</form>";
            ?>
        </div>
    </section>
</body>

</html>