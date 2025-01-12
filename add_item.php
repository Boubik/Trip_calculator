<?php
include "functions.php";
$conn = connect_db();
session_start();
if (!is_loged_in($conn, $_SESSION["username"], $_SESSION["password"]) || cant_see_itemset($conn, filter_input(INPUT_GET, "id"), $_SESSION["username"])) {
    header("Location: index.php");
}
$pageTitle = "Split Calculator | Add item";
$navbarItems = '
<li>
<a href="view.php?id=' . filter_input(INPUT_GET, "id") . '">Back</a>
</li>
<li>
    <a href="logout.php">Logout</a>
</li>
';
include "template.php";
?>

<main>

    <div class="container">
        <div class='center'>
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

            echo
            '
        <form method="POST" action="">
        <div>
        <label for="fname"">Payer</label>
        <select name="user">
        ';

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
            echo "</div>";
            ////////////////////////////////////////////////////////////////////////////////////////////ú
            echo '
        <div>
        <label for="fname">Category</label>
        ';
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
            }
            echo "</div>";
            ////////////////////////////////////////////////////////////////////////////////////////////ú
            echo
            '
        <div>
        <label for="fname">Currency</label>
        <select name="currency">
        ';
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
            echo "</div>";
            ////////////////////////////////////////////////////////////////////////////////////////////
            echo
            '
        <div class="txt_field">
        <input type="text" name="category2" placeholder="Category" value="">     
        <span></span>
        <label for="fname">Category</label>
        </div>
        <div class="txt_field">
        <input type="text" name="currency2" placeholder="Currency" value="">     
        <span></span>
        <label for="fname">Currency</label>
        </div>
        <div class="txt_field">
        <input type="number" min="0" step=0.01 name="price" placeholder="Price" value="">
        <span></span>
        <label for="fname">Price</label>
        </div>
        <div class="txt_field">
        <input type="text" name="note" placeholder="Aditional info" value="">
        <span></span>
        <label for="fname">Note</label>
        </div>

        <input type="submit" name="submit" value="Add">
        <div class="signup_link">
        </div>
        </form>
        </div>
        ';
            ?>
        </div>
    </div>
</main>
</body>

</html>