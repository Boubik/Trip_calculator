<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator</title>
    <link rel="stylesheet" href="style/default.css">
</head>

<body>
    <header>
        <h1><a href="/">Split calculator</a></h1>
        <a href="add.php">Add new item set</a>
    </header>

    <br>

    <section>
        <table id="first" style="margin-left:50vh; margin-top:30vh; transform: translate(-50%, -30%);">
            <?php
            error_reporting(0);
            include "functions.php";
            $conn = connect_db();
            if (!isset($_GET["people"])) {
                if (isset($_GET["error"])) {
                    switch (filter_input(INPUT_GET, "error")) {
                        case 1:
                            echo "name alredy exist";
                            break;
                        default:
                            echo "someting went wrong";
                            break;
                    }
                }
                echo "<form method=\"POST\" action=\"\">";
                echo "<tr>";
                echo "<th>";
                echo "<label for=\"fname\">Name:</label>";
                echo "</th>";
                echo "<th>";
                echo "<input type=\"text\" name=\"name\" placeholder=\"for example trip name\" value=\"\">";
                echo "</th>";
                echo "</tr>";
                echo "<tr>";
                echo "<th>";
                echo "<label for=\"lname\">Number of people:</label>";
                echo "</th>";
                echo "<th>";
                echo "<input type=\"number\" min=\"1\" name=\"people\" value=\"1\">";
                echo "</th>";
                echo "</tr>";
                echo "<tr>";
                echo "<th>";
                echo "</th>";
                echo "<th>";
                echo "<input type=\"submit\" name=\"submit\" value=\"Submit\">";
                echo "</th>";
                echo "</tr>";
                echo "</form>";

                if (isset($_POST["submit"])) {
                    $row = select($conn, "SELECT * FROM `item_set` WHERE `name` = \"" . filter_input(INPUT_POST, "name") . "\"");
                    if (!isset($row[0])) {
                        $sql = "INSERT INTO `item_set`(`name`, `people`) VALUES ('" . filter_input(INPUT_POST, "name") . "','" . filter_input(INPUT_POST, "people") . "')";
                        insert($conn, $sql);
                        header("Location: add.php?people=" . filter_input(INPUT_POST, "people") . "&name=" . filter_input(INPUT_POST, "name"));
                    } else {
                        header("Location: add.php?error=1");
                    }
                }
            } else {
                echo "<form method=\"POST\" action=\"\">";
                $i = 0;
                while ($i < filter_input(INPUT_GET, "people")) {
                    echo "<tr>";
                    echo "<th>";
                    echo "name of person number " . $i + 1;
                    echo "</th>";
                    echo "<th>";
                    echo "<input type=\"text\" name=\"people[" . $i . "]\" value=\"Honza\">";
                    echo "</th>";
                    echo "</tr>";
                    $i++;
                }
                echo "</tr>";
                echo "<tr>";
                echo "<th>";
                echo "</th>";
                echo "<th>";
                echo "<input type=\"submit\" name=\"submit\" value=\"Submit\">";
                echo "</th>";
                echo "</tr>";
                echo "</form>";

                if (isset($_POST["submit"])) {
                    $i = 0;

                    if (count(array_unique($_POST["people"])) < count($_POST["people"])) {
                        // Array has duplicates
                        echo "Names need to be unique between each other";
                    } else {
                        foreach ($_POST["people"] as $people) {
                            $people = ucfirst(strtolower($people));

                            $sql = "SELECT `name` FROM `user` WHERE `name` = \"" . $people . "\"";
                            $rows = select($conn, $sql);
                            if (!isset($rows[0])) {
                                $sql = "INSERT INTO `user`(`name`) VALUES ('" . $people . "')";
                                insert($conn, $sql);
                            }


                            $sql = "SELECT `user_name`, `item_set_name` FROM `user_has_item_set` WHERE `user_name` = \"" . $people . "\" AND `item_set_name` = \"" . filter_input(INPUT_GET, "name") . "\"";
                            $rows = select($conn, $sql);
                            if (!isset($rows[0])) {
                                $sql = "INSERT INTO `user_has_item_set`(`user_name`, `item_set_name`) VALUES ('" . $people . "','" . filter_input(INPUT_GET, "name") . "')";
                                insert($conn, $sql);
                            }
                        }
                        header("Location: view.php?name=" . filter_input(INPUT_GET, "name"));
                    }
                }
            }
            ?>
        </table>
    </section>

    <br>

    <footer>
        2O21
    </footer>

</html>
<?php
