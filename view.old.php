<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Split calculator | View</title>
    <link rel="stylesheet" href="style/default.css">
</head>

<body>
    <header>
        <h1><a href="index.php">Split calculator</a></h1>
        <?php
        echo "<a href=\"add_item.php?id=" . filter_input(INPUT_GET, "id") . "\">Add new item set</a>";
        echo "<a href=\"add_user.php?id=" . filter_input(INPUT_GET, "id") . "\">Add user</a>";
        ?>
        <script src="js/sorttable.js"></script>
    </header>

    <br>

    <aside>
        <?php
        include "functions.php";
        $conn = connect_db();
        session_start();
        if(!is_loged_in($conn, $_SESSION["username"], $_SESSION["password"]) || !cant_see_itemset($conn, filter_input(INPUT_GET, "id"), $_SESSION["username"])){
            header("Location: /");
        }
        
        if (isset($_POST["submit"])) {
            add_item(
                $conn,
                array(
                    "category" => ucfirst(strtolower(filter_input(INPUT_POST, "category"))),
                    "user" => filter_input(INPUT_POST, "user"),
                    "price" => filter_input(INPUT_POST, "price"),
                    "note" => filter_input(INPUT_POST, "note"),
                    "item_set" => filter_input(INPUT_GET, "name")
                )
            );
            unset($_POST);
            header("Location: view.php?name=" . $_GET["name"]);
        }
        ?>
        <div class="main">
        
        <h1 id="ppl" style="margin-top: 50px;">People:</h1>
        <?php
        #$sql = "SELECT `t1`.`name`, `t2`.`sum` FROM (SELECT `user`.`name` as `name` FROM `user` INNER JOIN `user_has_item_set` ON `user_has_item_set`.`user_name` = `user`.`name` INNER JOIN `item_set` ON `item_set`.`name` = `user_has_item_set`.`item_set_name` WHERE `item_set`.`id` = \"" . filter_input(INPUT_GET, "id") . "\") as `t1` LEFT JOIN (SELECT `user`.`name` as `name`, SUM(`price`) as `sum` FROM `item` INNER JOIN `user` ON `user`.`name` = `item`.`user_name` INNER JOIN `item_set` ON `item_set`.`name` = `item`.`item_set_name` WHERE `item_set`.`id` = \"" . filter_input(INPUT_GET, "id") . "\" GROUP by `user`.`name`) as `t2` ON `t1`.`name` = `t2`.`name` ORDER BY `t2`.`sum` DESC";
        $sql = "SELECT `t1`.`name`, `t2`.`sum` FROM (SELECT `user`.`name` as `name` FROM `user` INNER JOIN `user_has_item_set` ON `user_has_item_set`.`user_name` = `user`.`name` INNER JOIN `item_set` ON `item_set`.`id` = `user_has_item_set`.`item_set_id` WHERE `item_set`.`id` = '" . filter_input(INPUT_GET, "id") . "') as `t1` LEFT JOIN (SELECT `user`.`name` as `name`, SUM(`price`) as `sum` FROM `item` INNER JOIN `user` ON `user`.`name` = `item`.`payer` INNER JOIN `item_set` ON `item_set`.`id` = `item`.`item_set_id` WHERE `item_set`.`id` = '" . filter_input(INPUT_GET, "id") . "' GROUP by `user`.`name`) as `t2` ON `t1`.`name` = `t2`.`name` ORDER BY `t2`.`sum` DESC";
        $people = select($conn, $sql);
        
        #$sql = "SELECT SUM(`item`.`price`) as `sum`, `category`.`name` FROM `category` INNER JOIN `item` on `item`.`category_name` = `category`.`name` INNER JOIN `item_set` on `item`.`item_set_name` = `item_set`.`name` WHERE `item_set`.`id` = \"" . filter_input(INPUT_GET, "id") . "\" GROUP BY `category`.`name` ORDER BY `sum` DESC";
        $sql = "SELECT SUM(`item`.`price`) as `sum`, `category`.`name` FROM `category` INNER JOIN `item` on `item`.`category_name` = `category`.`name` INNER JOIN `item_set` on `item`.`item_set_id` = `item_set`.`id` WHERE `item_set`.`id` = '". filter_input(INPUT_GET, "id") ."' GROUP BY `category`.`name` ORDER BY `sum` DESC";
        $category = select($conn, $sql);
        echo "<table id='second'>";
        $max_price = 0;
        foreach ($people as $row) {
            echo "<tr>";
            echo "<th>";
            echo $row["name"];
            echo "</th>";

            echo "<th>";
            if (is_null($row["sum"])) {
                echo "0";
            } else {
                echo number_format($row["sum"], 2, ",", " ");
                $max_price += $row["sum"];
            }
            echo "</th>";
            echo "</tr>";
        }

        echo "<tr>";
        echo "</tr>";
        echo "<tr>";
        echo "</tr>";

        echo "<tr id='empty'>";
        echo "<th id='empty'>";
        echo "<th id='empty'>";
        echo "</th>";
        echo "</tr>";

        foreach ($category as $row) {
            echo "<tr>";
            echo "<th>";
            echo $row["name"];
            echo "</th>";

            echo "<th>";
            if (is_null($row["sum"])) {
                echo "0";
            } else {
                echo number_format($row["sum"], 2, ",", " ");
            }
            echo "</th>";
            echo "</tr>";
        }
        echo "<tr id='empty'>";
        echo "<th id='empty'>";
        echo "<th id='empty'>";
        echo "</th>";
        echo "</tr>";
        echo "<tr>";
        echo "<th>";
        echo "Full price";
        echo "</th>";
        echo "<th>";
        echo $max_price;
        echo "</th>";
        echo "</tr>";
        echo "<tr>";
        echo "<th>";
        echo "Price per person";
        echo "</th>";
        echo "<th>";
        echo number_format($max_price / count($people), 2, ".", " ");
        echo "</th>";
        echo "</tr>";
        echo "</table>";


        echo "<table id='third'>";

        echo "<tr>";
        echo "<th>";
        echo "from";
        echo "</th>";
        echo "<th>";
        echo "to";
        echo "</th>";
        echo "<th>";
        echo "how much";
        echo "</th>";
        echo "</tr>";

        echo "<br>";
        $peopleAlt = $people;
        while (!all_have_same_number($peopleAlt)) {
            $smallestId = find_smallest($peopleAlt);
            $bigestId = find_bigest($peopleAlt);
            if (pay($peopleAlt[$smallestId]["sum"], $max_price / count($peopleAlt)) == 0 or abs(pay(-$peopleAlt[$bigestId]["sum"], $max_price / count($peopleAlt))) == 0 or $bigestId == $smallestId) {
                break;
            } else {
                echo "<tr>";
                if (pay($peopleAlt[$smallestId]["sum"], $max_price / count($peopleAlt)) < abs(pay(-$peopleAlt[$bigestId]["sum"], $max_price / count($peopleAlt)))) {
                    $number = pay($peopleAlt[$smallestId]["sum"], $max_price / count($peopleAlt));
                } else {
                    $number = abs(pay(-$peopleAlt[$bigestId]["sum"], $max_price / count($peopleAlt)));
                }
                $peopleAlt[$smallestId]["sum"] += $number;
                $peopleAlt[$bigestId]["sum"] -= $number;

                echo "<th>";
                echo $peopleAlt[$smallestId]["name"];
                echo "</th>";
                echo "<th>";
                echo $peopleAlt[$bigestId]["name"];
                echo "</th>";
                echo "<th>";
                echo $number;
                echo "</th>";
                echo "</tr>";
            }
        }
        echo "</table>";
        ?>
    </aside>
    </div>
    <br>

    <section>
        <?php
        if (count($category) > 0) {
            $dataPeople = array();
            foreach ($people as $row) {
                $dataPeople[] = array("lable" => $row["name"], "y" => number_format(($row["sum"] / $max_price) * 100, 2, ".", " "));
            }
        }
        if (count($category) > 0) {
            $dataCategory = array();
            foreach ($category as $row) {
                $dataCategory[] = array("lable" => $row["name"], "y" => number_format(($row["sum"] / $max_price) * 100, 2, ".", " "));
            }
        }
        ?>
        <script>
            window.onload = function() {
                var chart = new CanvasJS.Chart("people", {
                    animationEnabled: true,
                    title: {
                        text: "people"
                    },
                    data: [{
                        type: "pie",
                        yValueFormatString: "#,##0.00\"%\"",
                        indexLabel: "{lable} ({y})",
                        dataPoints: <?php echo json_encode($dataPeople, JSON_NUMERIC_CHECK); ?>
                    }]
                });
                chart.render();

                var chart = new CanvasJS.Chart("category", {
                    animationEnabled: true,
                    title: {
                        text: "category"
                    },
                    data: [{
                        type: "pie",
                        yValueFormatString: "#,##0.00\"%\"",
                        indexLabel: "{lable} ({y})",
                        dataPoints: <?php echo json_encode($dataCategory, JSON_NUMERIC_CHECK); ?>
                    }]
                });
                chart.render();

            }
        </script>
        <div id="people" ></div>
        <div id="category"></div>
        <script src="js/canvasjs.min.js"></script>
    </section>

    <table class="sortable">
            <tr>
                <th>price</th>
                <th>category</th>
                <th>Paid</th>
                <th>Will pay</th>
                <th>note</th>
            </tr>

            <?php
            #$sql = "SELECT `item`.`price`, `item`.`note`, `item`.`category_name`, `item`.`user_name` FROM `item` INNER JOIN `category` ON `category`.`name` = `item`.`category_name` INNER JOIN `user` ON `user`.`name` = `item`.`user_name` INNER JOIN `item_set` ON `item_set`.`name` = `item`.`item_set_name` WHERE `item_set`.`name` = \"" . filter_input(INPUT_GET, "name") . "\" ORDER BY `item`.`price` DESC";
            $sql = "SELECT `item`.`id`, CONCAT(`item`.`price`, ' ', `currency_name`) as 'price', `item`.`note`, `item`.`category_name`, `item`.`payer`, `currency_name` FROM `item` INNER JOIN `category` ON `category`.`name` = `item`.`category_name` INNER JOIN `item_set` ON `item_set`.`id` = `item`.`item_set_id` WHERE `item_set`.`id` = '" . filter_input(INPUT_GET, "id") . "' ORDER BY `item`.`price` DESC";
            $rows = select($conn, $sql);
            foreach ($rows as $row) {
                echo "<tr>";
                echo "<th>" . $row["price"] . "</th>";
                echo "<th>" . $row["category_name"] . "</th>";
                echo "<th>" . $row["payer"] . "</th>";
                $sql = "SELECT GROUP_CONCAT(`user`.`name` SEPARATOR ', ') AS 'payer' FROM `user` INNER JOIN `item_has_user` ON `item_has_user`.`user_name` = `user`.`name` INNER JOIN `item` ON `item`.`id` = `item_has_user`.`item_id` WHERE `item`.`id` = '". $row["id"] ."'";
                $will_pay = select($conn, $sql)[0]["payer"];
                echo "<th>" . $will_pay . "</th>";
                echo "<th>" . $row["note"] . "</th>";
                echo " </tr>";
            }
            ?>
        </table>

</html>

