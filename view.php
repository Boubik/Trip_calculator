<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Trip calculator</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1><a href="index.php">Trip calculator</a></h1>
    </header>

    <aside>
        People: <br>
        <?php
        include "functions.php";
        $conn = connect_db();
        $sql = "SELECT `users`.`name` FROM `users`, `users_has_item_set`, `item_set` WHERE `users_has_item_set`.`users_name` = `users`.`name` AND `item_set`.`name` = \"" . filter_input(INPUT_GET, "name") . "\"";
        $rows = select($conn, $sql);
        foreach ($rows as $row) {
            echo $row["name"];
            echo "<br>";
        }
        ?>
    </aside>

    <section>
        chart atd
        <?php
        ?>
    </section>

    <footer>
        2O21
    </footer>

</html>
<?php
