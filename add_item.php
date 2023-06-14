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
    <main>

        <!-- echo
        '
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
        '; -->

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
                <label for="fname"">Payer: </label>
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
                echo "<label for=\"fname\"> or </label>";
            }
            echo
            '
            <input type="text" name="category2" placeholder="Category" value="">
            </div>
            ';
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
            echo "<label for=\"fname\"> or </label>";
            echo "<input type=\"text\" name=\"currency2\" placeholder=\"Currency\" value=\"\">";
            echo "</div>";

            echo
            '
            <div class="txt_field">
            <input type="text" name="currency2" placeholder="Currency" value="">     
            <span></span>
            <label for="fname">/label>
            </div>
            ';
            ////////////////////////////////////////////////////////////////////////////////////////////
            echo
            '
            <div class="txt_field">
            <input type="number" min="0" name="price" placeholder="Price" value="">
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
    </main>
</body>

</html>