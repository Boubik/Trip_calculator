<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator | Add item set</title>
    <link rel="stylesheet" href="style/default.css">
</head>

<body>
    <?php
    include "functions.php";
    $conn = connect_db();
    session_start();
    if(!is_loged_in($conn, $_SESSION["username"], $_SESSION["password"])){
        header("Location: /");
    }
    ?>

    <header>
        <h1><a href="/">Split calculator</a></h1>
    </header>

    <br>

    <section>
        <div class='container'>
            <?php
            if(isset($_POST["submit"])){
                $id = add_item_set($conn, $_POST["name"], $_SESSION["username"]);
                header("Location: view.php?id=".$id);
            }
            echo "<form method=\"POST\" action=\"\">";
            echo "<label for=\"fname\">Name:</label>";
            echo "<input type=\"text\" name=\"name\" placeholder=\"Item price\" value=\"\">";
            echo "<br>";
            echo "<input type=\"submit\" name=\"submit\" value=\"Add\">";
            echo "</form>";
            ?>
        </div>
    </section>
</body>

</html>