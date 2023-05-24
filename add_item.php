<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator | Add item</title>
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
        <h1><a href="/">Split calculator</a></h1>
    </header>

    <br>

    <section>
        <div class='container'>
            <?php
            if (isset($_POST["submit"])) {
                $array = array(
                    "id" => filter_input(INPUT_GET, "id"),
                    "user" => filter_input(INPUT_POST, "user"),
                    "category" => ucfirst(strtolower(filter_input(INPUT_POST, "category"))),
                    "category2" => ucfirst(strtolower(filter_input(INPUT_POST, "category2"))),
                    "price" => filter_input(INPUT_POST, "price"),
                    "currency" => ucfirst(filter_input(INPUT_POST, "currency")),
                    "currency2" => ucfirst(filter_input(INPUT_POST, "currency2")),
                    "note" => filter_input(INPUT_POST, "note")
                );
                $item_id = add_item($conn, $array);
                header("Location: add_users_to_item.php?id=" . filter_input(INPUT_GET, "id") . "&item_id=" . $item_id);
            }

            echo "<form method=\"POST\" action=\"\">";

            echo "<label for=\"fname\">Payer:</label>";
            echo "<select name=\"user\">";

            $i = 0;
            foreach (get_posible_payers($conn, filter_input(INPUT_GET, "id")) as $row) {
                if ($i == 0) {
                    echo "<option selected ";
                } else {
                    echo "<option ";
                }
                echo "value = \"" . $row["name"] . "\">" . $row["name"];
                echo "</option>";
                $i++;
            }
            echo "</select>";
            echo "<br>";

            echo "<label for=\"fname\">Category:</label>";
            if ((bool)count(get_categorys_for_item_set($conn, filter_input(INPUT_GET, "id")))) {
                echo "<select name=\"category\">";

                $i = 0;
                foreach (get_categorys_for_item_set($conn, filter_input(INPUT_GET, "id")) as $row) {
                    if ($i == 0) {
                        echo "<option selected ";
                    } else {
                        echo "<option ";
                    }
                    echo "value = \"" . $row["name"] . "\">" . $row["name"];
                    echo "</option>";
                    $i++;
                }

                echo "</select>";

                echo "<label for=\"fname\"> or </label>";
            }
            echo "<input type=\"text\" name=\"category2\" placeholder=\"category\" value=\"\">";
            echo "<br>";

            echo "<label for=\"fname\">Price:</label>";
            echo "<input type=\"number\" min=\"0\" name=\"price\" placeholder=\"Price\" value=\"\">";
            echo "<br>";

            echo "<label for=\"fname\">Currency:</label>";
            echo "<select name=\"currency\">";

            if ((bool)count(get_currency($conn))) {
                $i = 0;
                foreach (get_currency($conn) as $row) {
                    if ($i == 0) {
                        echo "<option selected ";
                    } else {
                        echo "<option ";
                    }
                    echo "value=\"" . $row["name"] . "\">" . $row["name"];
                    echo "</option>";
                    $i++;
                }
            }

            echo "</select>";

            echo "<label for=\"fname\"> or </label>";
            echo "<input type=\"text\" name=\"currency2\" placeholder=\"Currency\" value=\"\">";
            echo "<br>";

            echo "<label for=\"fname\">Note:</label>";
            echo "<input type=\"text\" name=\"note\" placeholder=\"Aditional info\" value=\"\">";
            echo "<br>";

            echo "<input type=\"submit\" name=\"submit\" value=\"Add\">";
            echo "</form>";
            ?>
        </div>
    </section>
</body>

</html>