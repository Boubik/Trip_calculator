<?php

function connect_db()
{
    $configs = include('config.php');
    $servername = $configs["servername"];
    $dbname = $configs["dbname"];
    $username = $configs["username"];
    $password = $configs["password"];

    $dsn = "mysql:host=$servername;dbname=$dbname;";
    //connect
    try {
        if (isset($password)) {
            $conn = new PDO($dsn, $username, $password);
        } else {
            $conn = new PDO($dsn, $username);
        }
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $execute = false;
    } catch (PDOException $e) {
        $execute = true;
        $dsn = "mysql:host=$servername;";
        //connect
        try {
            if (isset($password)) {
                $conn = new PDO($dsn, $username, $password);
            } else {
                $conn = new PDO($dsn, $username);
            }
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $execute = false;
            echo "Something goes wrong give us time to fix it";
        }

        $sql = $conn->prepare("SET character SET UTF8");
        $sql->execute();
    }
    return $conn;
}

function is_loged_in($conn, $username, $password)
{
    $sql = "SELECT * FROM `user` WHERE `name` = '" . $username . "' AND `password` = '" . $password . "'";
    $select = select($conn, $sql);
    return (bool)count($select);
}

function user_is_taken($conn, $username)
{
    $sql = "SELECT * FROM `user` WHERE `name` = '" . $username . "'";
    $select = select($conn, $sql);
    return (bool)count($select);
}

function register($conn, $username, $password)
{
    $sql = "INSERT INTO `user`(`name`, `password`) VALUES ('" . $username . "', '" . $password . "')";
    insert($conn, $sql);
}

function select($conn, $sql)
{
    $sql = $conn->prepare($sql);
    $sql->execute();
    $select = $sql->fetchAll();

    return $select;
}

function insert($conn, $sql)
{
    $sql = $conn->prepare($sql);
    $sql->execute();

    return $conn->lastInsertId();
}

function update($conn, $sql)
{
    $sql = $conn->prepare($sql);
    $sql->execute();
}

function delete($conn, $sql)
{
    $sql = $conn->prepare($sql);
    $sql->execute();
}

/**
 * will find the smallest number in array and return key
 */
function find_smallest($array)
{
    foreach ($array as $key => $item) {
        if ($item == "") {
            $item = 0;
        }
        if (!isset($number)) {
            $number = $item;
            $keyOfKey = $key;
        } else {
            if ($number > $item) {
                $number = $item;
                $keyOfKey = $key;
            }
        }
    }
    return $keyOfKey;
}

/**
 * will find the smallest number in array and return key
 */
function find_bigest($array)
{
    foreach ($array as $key => $item) {
        if (!isset($number)) {
            $number = $item;
            $keyOfKey = $key;
        } else {
            if ($number < $item) {
                $number = $item;
                $keyOfKey = $key;
            }
        }
    }
    return $keyOfKey;
}

/**
 * will will look if all numbers are same or not
 * within 0.01
 */
function all_have_same_number($array)
{
    foreach ($array as $item) {
        if (!isset($number)) {
            $number = $item;
        } else {
            if ($item == 0) {
                if ($number == 0 or abs(($item - $number) / $number) > 0.01) {
                    return false;
                }
            } else {
                if (abs(($number - $item) / $item) > 0.01) {
                    return false;
                }
            }
        }
    }
    return true;
}

/**
 * will calculate how much does $id item cost for one person
 */
function count_users_on_item($conn, $id)
{
    $sql = "SELECT `user`.`name` AS 'payer' FROM `user` INNER JOIN `item_has_user` ON `item_has_user`.`user_name` = `user`.`name` INNER JOIN `item` ON `item`.`id` = `item_has_user`.`item_id` WHERE `item`.`id` = '" . $id . "'";
    $payers = select($conn, $sql);
    return count($payers);
}

/**
 * will calculate how much does $id item cost for one person
 */
function price_per_item_for_one_person($conn, $id, $price)
{
    return ($price / count_users_on_item($conn, $id));
}

/**
 * will give you price for specific user in specific category after calculation
 */
function get_my_price_per_category($conn, $item_set_id, $user, $category, $currency)
{
    $sql = "SELECT item.price, item.id FROM item_set INNER JOIN item ON item_set.id = item.item_set_id INNER JOIN item_has_user ON item_has_user.item_id = item.id WHERE item_set.id = '" . $item_set_id . "' AND item_has_user.user_name = '" . $user . "' AND item.category_name = '" . $category . "' AND item.currency_name = '" . $currency . "';";
    $price = select($conn, $sql);
    return $price;
}

/**
 * will calculate how much $id spent after the calculation
 */
function user_spent_after_calculation($conn, $id, $currency, $name)
{
    $sum = 0;
    $sql = "SELECT item.id, item.price FROM item_set INNER JOIN item ON item_set.id = item.item_set_id INNER JOIN item_has_user ON item_has_user.item_id = item.id WHERE item_set.id = " . $id . " AND item_has_user.user_name = '" . $name . "' AND item.currency_name = '" . $currency . "';";
    $items = select($conn, $sql);
    foreach ($items as $item) {
        $sum += ($item["price"] / count_users_on_item($conn, $item["id"]));
    }
    return $sum;
}

/**
 * will add item and evriting necesery (category and will connect it together)
 */
function add_item($conn, $array)
{
    if (strlen($array["category2"]) > 0) {
        if (!in_array($array["category2"], strip_category_currency(get_category($conn)))) {
            $sql = "INSERT INTO `category`(`name`) VALUES ('" . $array["category2"] . "')";
            insert($conn, $sql);
        }
        $category = $array["category2"];
    } else {
        $category = $array["category"];
    }

    if (strlen($array["currency2"]) > 0) {
        if (!in_array($array["currency2"], strip_category_currency(get_currency($conn)))) {
            $sql = "INSERT INTO `currency`(`name`) VALUES ('" . $array["currency2"] . "')";
            insert($conn, $sql);
        }
        $currency = $array["currency2"];
    } else {
        $currency = $array["currency"];
    }
    $sql = "INSERT INTO `item`(`price`, `note`, `category_name`, `payer`, `item_set_id`, `currency_name`) VALUES ('" . $array["price"] . "','" . $array["note"] . "','" . $category . "','" . $array["user"] . "','" . $array["id"] . "','" . $currency . "')";
    return insert($conn, $sql);
}

function get_category($conn)
{
    $sql = "SELECT * FROM `category`";
    return select($conn, $sql);
}
function strip_category_currency($array)
{
    $new_array = array();
    foreach ($array as $item) {
        $new_array[] = $item["name"];
    }
    return $new_array;
}

function add_item_set($conn, $name, $owner)
{
    $sql = "INSERT INTO `item_set`(`name`, `owner`) VALUES ('" . $name . "','" . $owner . "')";
    $id = insert($conn, $sql);
    $sql = "INSERT INTO `user_has_item_set`(`item_set_id`, `user_name`) VALUES ('" . $id . "','" . $_SESSION["username"] . "')";
    insert($conn, $sql);
    return $id;
}

function cant_see_itemset($conn, $id, $username)
{
    $configs = include('config.php');
    if ($username == $configs["admin"]) {
        return true;
    }
    $sql = "SELECT `user_has_item_set`.`user_name` FROM `item_set` INNER JOIN `user_has_item_set` on  `item_set`.`id` = `user_has_item_set`.`item_set_id` WHERE `item_set`.`id` = '" . $id . "' AND `user_has_item_set`.`user_name` = '" . $username . "'";
    $select = select($conn, $sql);
    return (bool)count($select);
}

function user_has_item_set($conn, $id, $user)
{
    $sql = "INSERT INTO `user_has_item_set`(`item_set_id`, `user_name`) VALUES ('" . $id . "','" . $user . "')";
    return insert($conn, $sql);
}

function get_currency($conn)
{
    $sql = "SELECT * FROM `currency`";
    return select($conn, $sql);
}

function get_categorys_for_item_set($conn, $id)
{
    $sql = "SELECT `item`.`category_name` as 'name' FROM `item_set` INNER JOIN `item` ON `item_set`.`id` = `item`.`item_set_id` WHERE `item_set`.`id` = '" . $id . "' GROUP BY `item`.`category_name`";
    return select($conn, $sql);
}

function get_posible_payers($conn, $id)
{
    $sql = "SELECT `user_has_item_set`.`user_name` as 'name'  FROM `item_set` INNER JOIN `user_has_item_set` ON `user_has_item_set`.`item_set_id` = `item_set`.`id` WHERE `item_set`.`id` = '" . $id . "'";
    $select = select($conn, $sql);
    return $select;
}

function add_users_to_item($conn, $id, $users)
{
    foreach ($users as $user) {
        $sql = "INSERT INTO `item_has_user`(`item_id`, `user_name`) VALUES ('" . $id . "','" . $user . "')";
        insert($conn, $sql);
    }
}

function get_payer_for_item($conn, $id)
{
    $sql = "SELECT `payer` FROM `item` WHERE `id` = '" . $id . "'";
    return select($conn, $sql)[0]["payer"];
}

function update_item($conn, $array)
{
    $sql = "DELETE FROM `item_has_user` WHERE `item_has_user`.`item_id` = '" . $array["id"] . "'";
    delete($conn, $sql);
    add_users_to_item($conn, $array["id"], $array["users"]);
    update_category_for_item($conn, $array["category"]);
    update_currency_for_item($conn, $array["currency"]);
    $sql = "UPDATE `item` SET `price`='" . $array["price"] . "',`payer`='" . $array["payer"] . "',`note`='" . $array["note"] . "',`category_name`='" . $array["category"] . "',`currency_name`='" . $array["currency"] . "' WHERE `id` = '" . $array["id"] . "'";
    update($conn, $sql);
}

function update_category_for_item($conn, $category)
{
    $sql = "SELECT * FROM `category` WHERE `name` = '" . $category . "'";
    if (!(bool)count(select($conn, $sql))) {
        $sql = "INSERT INTO `category`(`name`) VALUES ('" . $category . "')";
        insert($conn, $sql);
    }
}

function update_currency_for_item($conn, $currency)
{
    $sql = "SELECT * FROM `currency` WHERE `name` = '" . $currency . "'";
    if (!(bool)count(select($conn, $sql))) {
        $sql = "INSERT INTO `currency`(`name`) VALUES ('" . $currency . "')";
        insert($conn, $sql);
    }
}

function delete_item($conn, $id)
{
    $sql = "DELETE FROM `item_has_user` WHERE `item_id` = '" . $id . "'";
    delete($conn, $sql);
    $sql = "DELETE FROM `item` WHERE `id` = '" . $id . "'";
    delete($conn, $sql);
}

function own_item_set($conn, $id, $name)
{
    $configs = include('config.php');
    $sql = "SELECT `owner` FROM `item_set` WHERE `id` = '" . $id . "' AND `owner` = '" . $name . "'";
    if ((bool)count(select($conn, $sql)) || $configs["admin"] == $name) {
        return true;
    } else {
        return false;
    }
}

function get_owner_of_item_set($conn, $id)
{
    $sql = "SELECT `owner` FROM `item_set` WHERE `id` = '" . $id . "'";
    return select($conn, $sql)[0]["owner"];
}
