<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1><a href="index.php">Split calculator</a></h1>
        <a href="add.php">Add new item set</a>
    </header>

    <br>

    <section>
        <?php
        include "functions.php";
        $conn = connect_db();
        $sql = "SELECT `name` FROM `item_set`";
        $rows = select($conn, $sql);

        foreach ($rows as $row) {
            echo "<a href=view.php?name=" . str_replace(' ', '%20', $row["name"]) . ">" . $row["name"] . "</a>";
            echo "<br>";
        }
        ?>
    </section>

    <br>

    <footer>
        2O21
    </footer>
</body>

</html>