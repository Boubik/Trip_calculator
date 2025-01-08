<?php
include "functions.php";
$conn = connect_db();
session_start();
if (isset($_POST["username"]) && own_item_set($conn, filter_input(INPUT_GET, "id"), $_SESSION["username"])) {
    header("Location: index.php");
}

$navbarItems = '
<li>
    <a href="view.php?id=' . filter_input(INPUT_GET, "id") . '">Back</a>
</li>
<li>
    <a href="logout.php">Logout</a>
</li>
';
$pageTitle = "Split calculator | Edit item set";
$head = "";
include "template.php";
?>

<section>
    <div class='container'>
        <?php
        $item_set = get_item_set($conn, filter_input(INPUT_GET, "id"));
        if (isset($_POST["submit"])) {
            if ($item_set["name"] !== $_POST["name"]) {
                update_item_set($conn, filter_input(INPUT_GET, "id"), filter_input(INPUT_POST, "name"));
                $item_set = get_item_set($conn, filter_input(INPUT_GET, "id"));
            }
            $editors = get_editors($conn, filter_input(INPUT_GET, "id"));
            $delete = array();
            $add = array();
            foreach (users_in_item_set_no_admin($conn, filter_input(INPUT_GET, "id")) as $row) {
                if (isset($_POST["editor"][$row["name"]])) {
                    if (!isset($editors[$row["name"]])) {
                        $add[] = $row["name"];
                    }
                } else {
                    if (isset($editors[$row["name"]])) {
                        $delete[] = $row["name"];
                    }
                }
            }

            if (count($delete) > 0) {
                delete_editors($conn, filter_input(INPUT_GET, "id"), $delete);
            }
            if (count($add) > 0) {
                add_editors($conn, filter_input(INPUT_GET, "id"), $add);
            }
            header("Location: view.php?id=" . filter_input(INPUT_GET, "id"));
        }

        // Name
        echo
        '
            <div class="center">
            <br>
            <form method="POST" action="">
            <div class="txt_field">
            <input type="text" name="name" value="';
        echo $item_set["name"];
        echo '" maxlength=100 required>
            <span></span>
            <label>Name</label>
            </div>';

        // Users
        echo "<label style=\"position: relative;color: #2691d9; top: -10px;\">Editors</label><br>";
        $editors = get_editors($conn, filter_input(INPUT_GET, "id"));
        foreach (users_in_item_set_no_admin($conn, filter_input(INPUT_GET, "id")) as $row) {
            if (isset($editors[$row["name"]])) {
                echo '<input type="checkbox" name="editor[' . $row["name"] . ']" checked>';
            } else {
                echo '<input type="checkbox" name="editor[' . $row["name"] . ']">';
            }
            echo '<span>' . $row["name"] . '</span><br>';
        }

        // Submit
        echo '
            <br>
            <input type="submit" name="submit" value="Save">
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