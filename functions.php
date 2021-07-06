<?php

function connect_db()
{
    $configs = include('config.php');
    $servername = $configs["servername"];
    $dbname = $configs["dbname"];
    $username = $configs["username"];
    //$password = $configs["password"];

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

/**
 * will find the smallest number in array and return key
 */
function find_smallest($array)
{
    foreach ($array as $key => $item) {
        if ($item["sum"] == "") {
            $item["sum"] = 0;
        }
        if (!isset($number)) {
            $number = $item["sum"];
            $keyOfKey = $key;
        } else {
            if ($number > $item["sum"]) {
                $number = $item["sum"];
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
            $number = $item["sum"];
            $keyOfKey = $key;
        } else {
            if ($number < $item["sum"]) {
                $number = $item["sum"];
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
            $number = $item["sum"];
        } else {
            if ($item["sum"] == 0) {
                if ($number == 0 or abs(($item["sum"] - $number) / $number) > 0.01) {
                    return false;
                }
            } else {
                if (abs(($number - $item["sum"]) / $item["sum"]) > 0.01) {
                    return false;
                }
            }
        }
    }
    return true;
}

/**
 * will calculate how much can pay or how much is needed
 * + can give
 * - need
 */
function pay($number, $perPerson)
{
    $number = number_format($number, 2, ".", "");
    $perPerson = number_format($perPerson, 2, ".", "");
    return number_format($number + $perPerson, 2, ".", "");
}

/**
 * will add item and evriting necesery (category and will connect it together)
 */
function add_item($conn, $array)
{
    $sql = "SELECT `name` FROM `category` WHERE `name` = \"" . $array["category"] . "\"";
    $rows = select($conn, $sql);
    if (count($rows) != 1) {
        $sql = "INSERT INTO `category`(`name`) VALUES ('" . $array["category"] . "')";
        insert($conn, $sql);
    }

    $sql = "INSERT INTO `item`(`price`, `note`, `category_name`, `user_name`, `item_set_name`) VALUES ('" . $array["price"] . "','" . $array["note"] . "','" . $array["category"] . "','" . $array["user"] . "','" . $array["item_set"] . "')";
    insert($conn, $sql);
}
