<?php

$pageTitle = 'Split Calculator | View';
$head = '
<script src="js/sorttable.js"></script>
<script src="js/chart.js"></script>';

include __DIR__ . '/functions.php';

$conn = connect_db();
start_secure_session();

$username = require_login($conn);
$itemSetId = get_int('id');

if ($itemSetId === null || cant_see_itemset($conn, $itemSetId, $username)) {
    redirect('index.php');
}

$itemSet = get_item_set($conn, $itemSetId);
if ($itemSet === null) {
    redirect('index.php');
}

$isOwner = own_item_set($conn, $itemSetId, $username);
$canEditItems = is_edditor_or_owner($conn, $itemSetId, $username);
$navbarItems = '';

if ($isOwner) {
    $navbarItems .= '
    <li>
        <a href="edit_item_set.php?id=' . (int)$itemSetId . '">Edit</a>
    </li>';
}

if ($canEditItems) {
    $navbarItems .= '
    <li>
        <a href="add_user.php?id=' . (int)$itemSetId . '">Add user</a>
    </li>';
}

$navbarItems .= '
<li>
    <a href="add_item.php?id=' . (int)$itemSetId . '">Add Item</a>
</li>
<li>
    <a href="edit_account.php">Edit Account</a>
</li>
<li>
    <a href="logout.php">Logout</a>
</li>
';

include __DIR__ . '/template.php';

$currencies = select(
    $conn,
    'SELECT `item`.`currency_name`
     FROM `item`
     WHERE `item`.`item_set_id` = :item_set_id
     GROUP BY `item`.`currency_name`
     ORDER BY `item`.`currency_name` ASC',
    array(':item_set_id' => $itemSetId)
);
$members = select(
    $conn,
    'SELECT `user`.`name`
     FROM `user_has_item_set`
     INNER JOIN `user` ON `user`.`name` = `user_has_item_set`.`user_name`
     WHERE `user_has_item_set`.`item_set_id` = :item_set_id
     ORDER BY `user`.`name` ASC',
    array(':item_set_id' => $itemSetId)
);
$people = array();

foreach ($currencies as $currencyRow) {
    $currencyName = $currencyRow['currency_name'];

    foreach ($members as $memberRow) {
        $memberName = $memberRow['name'];
        $spentRows = select(
            $conn,
            'SELECT SUM(`item`.`price`) AS `sum`, `item`.`currency_name` AS `currency`, `item`.`payer` AS `name`
             FROM `item`
             WHERE `item`.`item_set_id` = :item_set_id
               AND `item`.`payer` = :payer
               AND `item`.`currency_name` = :currency
             GROUP BY `item`.`currency_name`, `item`.`payer`',
            array(
                ':item_set_id' => $itemSetId,
                ':payer' => $memberName,
                ':currency' => $currencyName,
            )
        );

        if (count($spentRows) > 0) {
            $people[] = $spentRows[0];
        } else {
            $people[] = array(
                'name' => $memberName,
                'sum' => 0,
                'currency' => $currencyName,
            );
        }
    }
}

$categoryRows = select(
    $conn,
    'SELECT SUM(`item`.`price`) AS `sum`,
            `currency`.`name` AS `currency`,
            `category`.`name` AS `category`
     FROM `category`
     INNER JOIN `item` ON `item`.`category_name` = `category`.`name`
     INNER JOIN `item_set` ON `item`.`item_set_id` = `item_set`.`id`
     INNER JOIN `currency` ON `currency`.`name` = `item`.`currency_name`
     WHERE `item_set`.`id` = :item_set_id
     GROUP BY `currency`.`name`, `category`.`name`
     ORDER BY `currency`.`name` ASC, `sum` DESC',
    array(':item_set_id' => $itemSetId)
);
$items = select(
    $conn,
    "SELECT `item`.`id`,
            `item`.`price`,
            `item`.`currency_name`,
            `item`.`note`,
            `item`.`category_name`,
            `item`.`payer`,
            (
                SELECT GROUP_CONCAT(`user`.`name` ORDER BY `user`.`name` SEPARATOR ', ')
                FROM `user`
                INNER JOIN `item_has_user` ON `item_has_user`.`user_name` = `user`.`name`
                WHERE `item_has_user`.`item_id` = `item`.`id`
            ) AS `will_pay`
     FROM `item`
     WHERE `item`.`item_set_id` = :item_set_id
     ORDER BY `item`.`price` DESC, `item`.`id` DESC",
    array(':item_set_id' => $itemSetId)
);
?>

<main>
    <div class="container-view">
        <h1 class="heading" style="font-size: 60px"><?php echo e($itemSet['name']); ?></h1>

        <table id="second" class="styled-table">
            <caption>
                <h1 class="heading" id="ppl" style="font-size: 40px">by People</h1>
            </caption>
            <thead>
                <tr>
                    <th>Who</th>
                    <th>Before calculation</th>
                    <th>After calculation</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $dataPeople = array();
                $peopleWithCurrencies = array();

                foreach ($people as $row) {
                    echo '<tr>';
                    echo '<td>' . e($row['name']) . '</td>';
                    echo '<td>' . number_format((float)$row['sum'], 2, ',', ' ') . ' ' . e($row['currency']) . '</td>';

                    $afterCalculation = user_spent_after_calculation($conn, $itemSetId, $row['currency'], $row['name']);
                    $peopleWithCurrencies[$row['currency']][$row['name']] = (float)$row['sum'] - $afterCalculation;
                    $dataPeople[] = array('label' => $row['name'], 'value' => $afterCalculation);

                    echo '<td>' . number_format($afterCalculation, 2, ',', ' ') . ' ' . e($row['currency']) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        <section class="graf"><canvas id="PeopleGraf"></canvas></section>

        <table id="second" class="styled-table">
            <caption>
                <h1 id="ppl" class="heading" style="font-size: 40px">by Category</h1>
            </caption>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Everyone price</th>
                    <th>My Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $dataCategory = array();
                $sumAll = array();

                foreach ($categoryRows as $row) {
                    if (!isset($sumAll[$row['currency']])) {
                        $sumAll[$row['currency']] = array(
                            'everyone' => 0,
                            'me' => 0,
                        );
                    }

                    echo '<tr>';
                    echo '<td>' . e($row['category']) . '</td>';
                    echo '<td>' . number_format((float)$row['sum'], 2, ',', ' ') . ' ' . e($row['currency']) . '</td>';

                    $sumAll[$row['currency']]['everyone'] += (float)$row['sum'];

                    $mySum = 0;
                    foreach (get_my_price_per_category($conn, $itemSetId, $username, $row['category'], $row['currency']) as $item) {
                        $count = count_users_on_item($conn, $item['id']);
                        if ($count > 0) {
                            $mySum += ((float)$item['price'] / $count);
                        }
                    }

                    $sumAll[$row['currency']]['me'] += $mySum;
                    $dataCategory[] = array('label' => $row['category'], 'value' => $mySum);

                    echo '<td>' . number_format($mySum, 2, ',', ' ') . ' ' . e($row['currency']) . '</td>';
                    echo '</tr>';
                }

                if (count($sumAll) > 0) {
                    echo '<tr><td id="empty" colspan="3"></td></tr>';
                }

                foreach ($sumAll as $currency => $sum) {
                    echo '<tr>';
                    echo '<td>' . e('All ' . $currency) . '</td>';
                    echo '<td>' . number_format($sum['everyone'], 2, ',', ' ') . ' ' . e($currency) . '</td>';
                    echo '<td>' . number_format($sum['me'], 2, ',', ' ') . ' ' . e($currency) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        <section class="graf"><canvas id="CategoryGraf"></canvas></section>

        <table id="second" class="styled-table">
            <caption>
                <h1 id="ppl" class="heading" style="font-size: 40px">Calculated payback</h1>
            </caption>
            <thead>
                <tr>
                    <th>From</th>
                    <th>To</th>
                    <th>How much</th>
                </tr>
            </thead>
            <?php
            $currencyIndex = 0;
            foreach ($peopleWithCurrencies as $currency => $usersPerCurrency) {
                if ($currencyIndex !== 0) {
                    echo '<tr><td id="empty" colspan="3"></td></tr>';
                }

                while (!all_have_same_number($usersPerCurrency)) {
                    $smallestId = find_smallest($usersPerCurrency);
                    $biggestId = find_bigest($usersPerCurrency);

                    if (
                        $usersPerCurrency[$smallestId] == 0 ||
                        abs($usersPerCurrency[$biggestId]) == 0 ||
                        $usersPerCurrency[$biggestId] == $usersPerCurrency[$smallestId]
                    ) {
                        break;
                    }

                    if ($usersPerCurrency[$smallestId] < abs($usersPerCurrency[$biggestId])) {
                        $number = $usersPerCurrency[$smallestId];
                    } else {
                        $number = abs($usersPerCurrency[$biggestId]);
                    }

                    $usersPerCurrency[$smallestId] -= $number;
                    $usersPerCurrency[$biggestId] += $number;

                    echo '<tr>';
                    echo '<td>' . e($smallestId) . '</td>';
                    echo '<td>' . e($biggestId) . '</td>';
                    echo '<td>' . number_format(-$number, 2, ',', ' ') . ' ' . e($currency) . '</td>';
                    echo '</tr>';
                }

                $currencyIndex++;
            }
            ?>
        </table>

        <div class="table-container">
            <table class="styled-table">
                <caption>
                    <h1 id="ppl" class="heading" style="font-size: 40px">
                        Items
                        <?php
                        $owner = get_owner_of_item_set($conn, $itemSetId);
                        if ($owner !== null) {
                            echo ' -> owner is ' . e($owner);
                        }
                        ?>
                    </h1>
                </caption>
                <thead>
                    <tr>
                        <th>Price</th>
                        <th>One person</th>
                        <th>Category</th>
                        <th>Paid</th>
                        <th>Will pay</th>
                        <th>Note</th>
                        <?php if ($canEditItems): ?>
                            <th colspan="2">Controls</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $row): ?>
                        <tr>
                            <td><?php echo number_format((float)$row['price'], 2, ',', ' ') . ' ' . e($row['currency_name']); ?></td>
                            <td><?php echo number_format(price_per_item_for_one_person($conn, $row['id'], $row['price']), 2, ',', ' ') . ' ' . e($row['currency_name']); ?></td>
                            <td><?php echo e($row['category_name']); ?></td>
                            <td><?php echo e($row['payer']); ?></td>
                            <td><?php echo e($row['will_pay']); ?></td>
                            <td><?php echo e($row['note']); ?></td>
                            <?php if ($canEditItems): ?>
                                <td>
                                    <a href="edit_item.php?id=<?php echo (int)$row['id']; ?>&amp;back=<?php echo (int)$itemSetId; ?>">
                                        <img src="./images/edit-svgrepo-com.svg" width="24" height="24" title="Edit" alt="Edit">
                                    </a>
                                </td>
                                <td>
                                    <a href="edit_item.php?id=<?php echo (int)$row['id']; ?>&amp;back=<?php echo (int)$itemSetId; ?>&amp;delete=true">
                                        <img src="./images/delete-button-svgrepo-com.svg" width="24" height="24" title="Delete" alt="Delete">
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    var peopleData = <?php echo json_encode($dataPeople, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    var peopleContext = document.getElementById("PeopleGraf").getContext("2d");

    new Chart(peopleContext, {
        type: "pie",
        data: {
            labels: peopleData.map(function (item) {
                return item.label;
            }),
            datasets: [{
                data: peopleData.map(function (item) {
                    return item.value;
                })
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "right",
                    labels: {
                        color: "white",
                        font: {
                            size: 16,
                            weight: "bold"
                        }
                    }
                }
            }
        }
    });

    var categoryData = <?php echo json_encode($dataCategory, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    var categoryContext = document.getElementById("CategoryGraf").getContext("2d");

    new Chart(categoryContext, {
        type: "pie",
        data: {
            labels: categoryData.map(function (item) {
                return item.label;
            }),
            datasets: [{
                data: categoryData.map(function (item) {
                    return item.value;
                })
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "left",
                    labels: {
                        color: "white",
                        font: {
                            size: 16,
                            weight: "bold"
                        }
                    }
                }
            }
        }
    });
</script>
</body>

</html>
