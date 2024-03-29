<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator | Edit item</title>
    <link rel="stylesheet" href="style/default.css">
</head>

<body>
    <?php
    include "functions.php";
    $conn = connect_db();
    session_start();
    if (!is_loged_in($conn, $_SESSION["username"], $_SESSION["password"]) || !isset($_GET["back"]) || !isset($_GET["id"]) || !own_item_set($conn, filter_input(INPUT_GET, "back"), $_SESSION["username"])) {
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
            if (isset($_GET["delete"])) {
                if (isset($_POST["submit"])) {
                    if ($_POST["submit"] == "Delte") {
                        delete_item($conn, filter_input(INPUT_GET, "id"));
                    }
                    header("Location: view.php?id=" . filter_input(INPUT_GET, "back"));
                }
                echo "Delete this item?";
                echo "<br>";
                echo "<form method=\"POST\" action=\"\">";
                echo "<input type=\"submit\" name=\"submit\" value=\"Delte\">";
                echo "<input type=\"submit\" name=\"submit\" value=\"Cancle\">";
                echo "</form>";
            } else {
                $sql = "SELECT * FROM `item` WHERE id = '" . filter_input(INPUT_GET, "id") . "'";
                $data = select($conn, $sql)[0];
                if (isset($_POST["submit"])) {
                    if ($_POST["submit"] == "Cancle") {
                        header("Location: view.php?id=" . filter_input(INPUT_GET, "back"));
                    }
                    $array = array(
                        "id" => filter_input(INPUT_GET, "id"),
                        "payer" => filter_input(INPUT_POST, "payer"),
                        "users" => $_POST["users"],
                        "category" => ucfirst(strtolower(filter_input(INPUT_POST, "category"))),
                        "category2" => ucfirst(strtolower(filter_input(INPUT_POST, "category2"))),
                        "price" => filter_input(INPUT_POST, "price"),
                        "currency" => ucfirst(filter_input(INPUT_POST, "currency")),
                        "currency2" => ucfirst(filter_input(INPUT_POST, "currency2")),
                        "note" => filter_input(INPUT_POST, "note")
                    );
                    if ($data["category_name"] != $array["category"] || $data["category_name"] != $array["category2"]) {
                        if ($data["category_name"] != $array["category2"]) {
                            $array["category"] = $array["category2"];
                        }
                    }
                    update_item($conn, $array);
                    header("Location: view.php?id=" . filter_input(INPUT_GET, "back"));
                }
                echo "<form method=\"POST\" action=\"\">";
                echo "<label for=\"fname\">Price:</label>";
                echo "<input type=\"number\" name=\"price\" value=\"" . $data["price"] . "\">";
                echo "<br>";

                echo "<label for=\"fname\">Payer:</label>";
                echo "<select name=\"payer\">";

                foreach (get_posible_payers($conn, filter_input(INPUT_GET, "back")) as $row) {
                    if (get_payer_for_item($conn, filter_input(INPUT_GET, "id")) == $row["name"]) {
                        echo "<option selected ";
                    } else {
                        echo "<option ";
                    }
                    echo "value = \"" . $row["name"] . "\">" . $row["name"];
                    echo "</option>";
                }
                echo "</select>";
                echo "<br>";

                $sql = "SELECT `user_has_item_set`.`user_name`, `user_has_item_set`.`user_name` IN (SELECT `user_name` FROM `item` INNER JOIN `item_has_user` ON `item_has_user`.`item_id` = `item`.`id` WHERE `item`.`id` = '" . filter_input(INPUT_GET, "id") . "') as 'checked' FROM `user_has_item_set` WHERE `user_has_item_set`.`item_set_id` = (SELECT `item_set_id` FROM `item` WHERE `item`.`id` = '" . filter_input(INPUT_GET, "id") . "')";
                $users = select($conn, $sql);
                echo "<label for=\"fname\">Will pay:</label>";
                foreach ($users as $user) {
                    if ($user["checked"]) {
                        echo "<input type=\"checkbox\" name=\"users[]\" value=\"" . $user["user_name"] . "\" checked>" . $user["user_name"] . "";
                    } else {
                        echo "<input type=\"checkbox\" name=\"users[]\" value=\"" . $user["user_name"] . "\">" . $user["user_name"] . "";
                    }
                }
                echo "<br>";

                echo "<label for=\"fname\">Note:</label>";
                echo "<input type=\"text\" name=\"note\" value=\"" . $data["note"] . "\">";
                echo "<br>";

                $sql = "SELECT `item`.`category_name` as 'category' FROM `item` WHERE `item`.`id` = '" . filter_input(INPUT_GET, "id") . "'";
                $cur_category = select($conn, $sql)[0]["category"];
                echo "<label for=\"fname\">Category:</label>";
                echo "<select name=\"category\">";
                foreach (get_categorys_for_item_set($conn, filter_input(INPUT_GET, "back")) as $row) {
                    if ($row["name"] == $cur_category) {
                        echo "<option selected ";
                    } else {
                        echo "<option ";
                    }
                    echo "value = \"" . $row["name"] . "\">" . $row["name"];
                    echo "</option>";
                }
                echo "</select>";
                echo " or ";
                echo "<input type=\"text\" name=\"category2\" value=\"" . $data["category_name"] . "\">";
                echo "<br>";

                echo "<label for=\"fname\">Currency:</label>";
                echo "<select name=\"currency\">";
                if ((bool)count(get_currency($conn))) {
                    foreach (get_currency($conn) as $row) {
                        if ($data["currency_name"] == $row["name"]) {
                            echo "<option selected ";
                        } else {
                            echo "<option ";
                        }
                        echo "value=\"" . $row["name"] . "\">" . $row["name"];
                        echo "</option>";
                    }
                }
                echo "</select>";
                echo " or ";
                echo "<input type=\"text\" name=\"currency2\" value=\"" . $data["currency_name"] . "\">";
                echo "<br>";
                echo "<input type=\"submit\" name=\"submit\" value=\"Edit\">";
                echo "<input type=\"submit\" name=\"submit\" value=\"Cancle\">";
                echo "</form>";
            }
            ?>
        </div>
    </section>
</body>

</html>