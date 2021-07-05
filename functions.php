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
