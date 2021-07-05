<?php
include "functions.php";
echo "hi";

$conn = connect_db();
$rows = select($conn, "SELECT * FROM `item_set`");

echo "<br>";
echo "<br>";

foreach ($rows as $row) {
    print_r($row);
    echo "<br>";
}
