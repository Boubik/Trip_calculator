<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator | Add users to item</title>
    <link rel="stylesheet" href="style/default.css">
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
        <section class="navigation">
            <div class="nav-container">
                <div class="brand">
                    <a href="index.php">
                        <img src="./images/icons8-calculator.svg" alt="">
                        Split-Calculator
                    </a>
                </div>

                <nav>
                    <div class="nav-mobile"><a id="navbar-toggle" href="#!"><span></span></a></div>
                    <ul class="nav-list">
                        <li>
                            <a href="index.php">Home</a>
                        </li>
                        <li>

                        </li>
                        <li>
                            <a href="add_itemset.php">Add Trip</a>
                        </li>
                        <li>
                            <a href="logout.php">Logout</a>
                        </li>

                    </ul>
                </nav>
            </div>
        </section>
    </header>
    <br>
    <section>
        <div class='container' style="color: #ffffff">
            <?php
            if (isset($_POST["submit"])) {
                print_r($_POST["users"]);
                add_users_to_item($conn, filter_input(INPUT_GET, "item_id"), $_POST["users"]);
                header("Location: view.php?id=" . filter_input(INPUT_GET, "id"));
            }

            echo "<h2>The people that will pay it</h2>";

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

            echo "<br>";
            echo "<input type=\"submit\" name=\"submit\" value=\"Add\">";
            echo "</form>";
            ?>
        </div>
    </section>
</body>

</html>