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
        include "functions.php";
        $conn = connect_db();
        session_start();
        echo "<a href=\"view.php?id=" . filter_input(INPUT_GET, "id") . "&share=" . $_SESSION["username"] . "\">Share</a>";
        echo "<a href=\"add_user.php?id=" . filter_input(INPUT_GET, "id") . "\">Add user</a>";
        echo "<a href=\"add_item.php?id=" . filter_input(INPUT_GET, "id") . "\">Add new item</a>";
        ?>
        <script src="js/sorttable.js"></script>
        <script src="js/chart.js"></script>
    </header>

    <br>

    <aside>
        <?php
        if (!is_loged_in($conn, $_SESSION["username"], $_SESSION["password"]) || (!cant_see_itemset($conn, filter_input(INPUT_GET, "id"), $_SESSION["username"]) && true)) {
            if (!isset($_GET["share"])) {
                header("Location: index.php");
            }
            $_SESSION["username"] = filter_input(INPUT_GET, "share");
        }
        ?>
        <div class="main">

            <h1 id="ppl" style="margin-top: 50px;">People:</h1>
            <?php
            $sql = "SELECT `item`.`currency_name` FROM `item_set` INNER JOIN `item` ON `item`.`item_set_id` = `item_set`.`id` WHERE `item_set_id` = '" . filter_input(INPUT_GET, "id") . "' GROUP BY `item`.`currency_name`";
            $people = array();
            $currancys = select($conn, $sql);
            foreach ($currancys as $currancy) {
                $currancy = $currancy["currency_name"];
                $sql = "SELECT `user`.`name` FROM `user_has_item_set` INNER JOIN `user` ON `user`.`name` = `user_has_item_set`.`user_name` WHERE `user_has_item_set`.`item_set_id` = '" . filter_input(INPUT_GET, "id") . "'";
                $select = select($conn, $sql);
                foreach ($select as $user) {
                    $user = $user["name"];
                    $sql = "SELECT sum(item.price) as 'sum', item.currency_name as 'currency', item.payer as 'name' FROM item_set INNER JOIN item ON item_set.id = item.item_set_id WHERE item_set.id = '" . filter_input(INPUT_GET, "id") . "' AND `item`.`payer` = '" . $user . "' AND `item`.`currency_name` = '" . $currancy . "' GROUP BY item.currency_name, item.payer;";
                    $select_people = select($conn, $sql);
                    if (count($select_people) > 0) {
                        $select_people = $select_people[0];
                    } else {
                        $select_people["name"] = $user;
                        $select_people["sum"] = 0;
                        $select_people["currency"] = $currancy;
                    }
                    $people[] = $select_people;
                }
            }

            $sql = "SELECT SUM(`item`.`price`) as `sum`, `currency`.`name` as 'currency', `category`.`name` as 'category' FROM `category` INNER JOIN `item` on `item`.`category_name` = `category`.`name` INNER JOIN `item_set` on `item`.`item_set_id` = `item_set`.`id` INNER JOIN `currency` ON `currency`.`name` = `item`.`currency_name` WHERE `item_set`.`id` = '" . filter_input(INPUT_GET, "id") . "' GROUP BY `currency`.`name`, `category`.`name` ORDER BY 'currency' ASC, `sum` DESC";
            $category = select($conn, $sql);
            ?>
            <table id='second'>
                <tr>
                    <th>
                        Who
                    </th>
                    <th>
                        Before calculation
                    </th>
                    <th>
                        After calculation
                    </th>
                </tr>
                <tr>
                    <td id='empty' colspan="3"></td>
                </tr>

                <?php
                $dataPeople = array();
                $people_with_currencys = array();
                foreach ($people as $row) {
                    echo "<tr>";
                    echo "<td>";
                    echo $row["name"];
                    echo "</td>";

                    echo "<td>";
                    if (is_null($row["sum"])) {
                        echo "0";
                    } else {
                        echo number_format($row["sum"], 2, ",", " ") . " " . $row["currency"];
                    }
                    echo "</td>";
                    echo "<td>";
                    $people_with_currencys[$row["currency"]][$row["name"]] = user_spent_after_calculation($conn, filter_input(INPUT_GET, "id"), $row["currency"], $row["name"]);
                    $dataPeople[] = array("label" => $row["name"], "value" => $people_with_currencys[$row["currency"]][$row["name"]]);
                    echo number_format($people_with_currencys[$row["currency"]][$row["name"]], 2, ",", " ") . " " . $row["currency"];
                    $people_with_currencys[$row["currency"]][$row["name"]] = $row["sum"] - $people_with_currencys[$row["currency"]][$row["name"]];
                    echo "</td>";
                    echo "</tr>";
                }
                ?>

                <h1 id="ppl" style="margin-top: 50px;">Category:</h1>
                <table id='second'>
                    <tr>
                        <th>
                            Category
                        </th>
                        <th>
                            Everyone price
                        </th>
                        <th>
                            My Price
                        </th>
                    </tr>
                    <tr>
                        <td id='empty' colspan="3"></td>
                    </tr>
                    <?php
                    $dataCategory = array();
                    $sumAll = array();
                    foreach ($category as $row) {
                        $sumAll[$row["currency"]]["everyone"] = 0;
                        $sumAll[$row["currency"]]["me"] = 0;
                        echo "<tr>";
                        echo "<td>";
                        echo $row["category"];
                        echo "</td>";

                        echo "<td>";
                        if (is_null($row["sum"])) {
                            echo "0";
                        } else {
                            echo number_format($row["sum"], 2, ",", " ") . " " . $row["currency"];
                            $sumAll[$row["currency"]]["everyone"] += $row["sum"];
                        }
                        echo "</td>";
                        echo "<th>";
                        $sum = 0;
                        foreach (get_my_price_per_category($conn, filter_input(INPUT_GET, "id"), $_SESSION["username"], $row["category"], $row["currency"]) as $item) {
                            $sum += ($item["price"] / count_users_on_item($conn, $item["id"]));
                        }
                        $sumMe = $sum;
                        $sumAll[$row["currency"]]["me"] += $sum;
                        $dataCategory[] = array("label" => $row["category"], "value" => $sum);
                        echo number_format($sum, 2, ",", " ") . " " . $row["currency"];
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "<tr>";
                    echo "<td id='empty' colspan=\"3\"></td>";
                    echo "</tr>";
                    foreach ($sumAll as $currency => $sum) {
                        echo "<tr>";
                        echo "<td>";
                        echo "All " . $currency;
                        echo "</td>";
                        echo "<td>";
                        echo number_format($sum["everyone"], 2, ",", " ") . " " . $currency;
                        echo "</td>";
                        echo "<td>";
                        echo number_format($sum["me"], 2, ",", " ") . " " . $currency;
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>

                <section>
                    <canvas id="PeopleGraf"></canvas>
                    <canvas id="CategoryGraf"></canvas>
                </section>

                <!--<script>
                    var data = <?php //echo json_encode($dataPeople); 
                                ?>;

                    var ctx = document.getElementById('PeopleGraf').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: data.map(item => item.label),
                            datasets: [{
                                data: data.map(item => item.value)
                            }]
                        },
                        options: {
                            responsive: true
                        }
                    });
                    var data = <?php //echo json_encode($dataCategory); 
                                ?>;

                    var ctx = document.getElementById('CategoryGraf').getContext('2d');
                    var myChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: data.map(item => item.label),
                            datasets: [{
                                data: data.map(item => item.value)
                            }]
                        },
                        options: {
                            responsive: true
                        }
                    });
                </script>-->


                <h1 id="ppl" style="margin-top: 50px;">Calated payback:</h1>
                <table id='second'>
                    <tr>
                        <th>
                            From
                        </th>
                        <th>
                            To
                        </th>
                        <th>
                            How much
                        </th>
                    </tr>
                    <tr>
                        <td id='empty' colspan="3"></td>
                    </tr>
                    <?php

                    ?>
                    <?php
                    $i = 0;
                    foreach ($people_with_currencys as $currency => $users_per_curency) {
                        if ($i != 0) {
                            echo "<tr>";
                            echo "<td id='empty' colspan=\"3\"></td>";
                            echo "</tr>";
                        }
                        while (!all_have_same_number($users_per_curency)) {
                            $smallestId =  find_smallest($users_per_curency);
                            $bigestId = find_bigest($users_per_curency);
                            if ($users_per_curency[$smallestId] == 0 or abs($users_per_curency[$bigestId]) == 0 or $users_per_curency[$bigestId] == $users_per_curency[$smallestId]) {
                                break;
                            } else {
                                echo "<tr>";
                                if ($users_per_curency[$smallestId] < abs($users_per_curency[$bigestId])) {
                                    $number = $users_per_curency[$smallestId];
                                } else {
                                    $number = abs($users_per_curency[$bigestId]);
                                }
                                $users_per_curency[$smallestId] -= $number;
                                $users_per_curency[$bigestId] += $number;

                                echo "<th>";
                                echo $smallestId;
                                echo "</th>";
                                echo "<th>";
                                echo $bigestId;
                                echo "</th>";
                                echo "<th>";
                                echo number_format(-$number, 2, ",", " ") . " " . $currency;
                                echo "</th>";
                                echo "</tr>";
                            }
                        }
                        $i++;
                    }
                    ?>
                </table>
    </aside>
    </div>
    <br>
    <h1 id="ppl" style="margin-top: 50px;">Items:</h1>
    <table class="sortable">
        <tr>
            <th>price</th>
            <th>One person</th>
            <th>category</th>
            <th>Paid</th>
            <th>Will pay</th>
            <th>note</th>
            <?php
            if (own_item_set($conn, filter_input(INPUT_GET, "id"), $_SESSION["username"]) && !isset($_GET["share"])) {
                echo "<th>Control</th>";
            }
            ?>
        </tr>
        <tr>
            <td id='empty' colspan="7"></td>
        </tr>

        <?php
        $sql = "SELECT `item`.`id`, `item`.`price`, `currency_name`, `item`.`note`, `item`.`category_name`, `item`.`payer`, `currency_name`, (SELECT GROUP_CONCAT(`user`.`name` SEPARATOR ', ') AS 'payer' FROM `user` INNER JOIN `item_has_user` ON `item_has_user`.`user_name` = `user`.`name` INNER JOIN `item` `i` ON `i`.`id` = `item_has_user`.`item_id` WHERE `item`.`id` = i.id) as 'will_pay' FROM `item` INNER JOIN `category` ON `category`.`name` = `item`.`category_name` INNER JOIN `item_set` ON `item_set`.`id` = `item`.`item_set_id` WHERE `item_set`.`id` = '" . filter_input(INPUT_GET, "id") . "' ORDER BY `item`.`price` DESC";
        $rows = select($conn, $sql);
        foreach ($rows as $row) {
            echo "<tr>";
            echo "<td>" . number_format($row["price"], 2, ",", " ") . " " . $row["currency_name"] . "</td>";
            echo "<td>" . number_format(price_per_item_for_one_person($conn, $row["id"], $row["price"]), 2, ",", " ") . " " . $row["currency_name"] . "</td>";
            echo "<td>" . $row["category_name"] . "</td>";
            echo "<td>" . $row["payer"] . "</td>";
            echo "<td>" . $row["will_pay"] . "</td>";
            echo "<td>" . $row["note"] . "</td>";
            if (own_item_set($conn, filter_input(INPUT_GET, "id"), $_SESSION["username"]) && !isset($_GET["share"])) {
                echo "<td><a href=\"edit_item.php?id=" . $row["id"] . "&back=" . filter_input(INPUT_GET, "id") . "\">Edit</a> <td><a href=\"edit_item.php?id=" . $row["id"] . "&back=" . filter_input(INPUT_GET, "id") . "&delete=true\">Delete</a></td>";
            }
            echo " </tr>";
        }
        echo "owner is: " . get_owner_of_item_set($conn, filter_input(INPUT_GET, "id"));
        ?>
    </table>

</html>