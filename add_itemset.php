<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $pageTitle = "Split Calculator | Add Trip";
    ?>
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
    <?php
    $navbarItems = '
        <li>
            <a href="add_itemset.php">Add Trip</a>
        </li>';
    include "template.php";
    ?>

    <main>
        <div class='container'>
            <?php
            if (isset($_POST["submit"])) {
                $id = add_item_set($conn, $_POST["name"], $_SESSION["username"]);
                header("Location: view.php?id=" . $id);
            }
            echo '
            <div class="center">
                <form method="POST" action="">
                    <div class="txt_field">
                            <input type="text" name="name" placeholder="Name of the trip" value="">                             
                            <span></span>
                        <label>Name</label>
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