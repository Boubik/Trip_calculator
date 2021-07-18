<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator</title>
    <link rel="stylesheet" href="style/default.css">
</head>

<body>
    <header>
        <h1><a href="index.php">Split calculator</a></h1>
        <a href="add.php">Add new item set</a>
    </header>

    <br>

    <section>
        <div class='main'>
            <?php
            include "functions.php";
            $conn = connect_db();
            $sql = "SELECT `name` FROM `item_set`";
            $rows = select($conn, $sql);

            
            foreach ($rows as $row) {
                echo "<div id='grid'><a id='item' href=view.php?name=" . str_replace(' ', '%20', $row["name"]) . ">" . $row["name"] . "</a></div>";
            }
            ?>
        </div>
    </section>

    <br>

    <footer>
        <p>2021</p>
    </footer>
</body>

</html>