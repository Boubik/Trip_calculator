<?php

error_reporting(E_ALL);
ini_set('display_errors', '0');

function get_config()
{
    static $config = null;

    if ($config === null) {
        $config = include __DIR__ . '/config.php';
    }

    return $config;
}

function connect_db()
{
    static $conn = null;

    if ($conn instanceof PDO) {
        return $conn;
    }

    $configs = get_config();
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $configs['servername'],
        $configs['dbname']
    );

    try {
        $conn = new PDO(
            $dsn,
            $configs['username'],
            $configs['password'],
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            )
        );
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Something went wrong. Please try again later.');
    }

    migrate_all_legacy_password_rows($conn);

    return $conn;
}

function send_security_headers()
{
    if (headers_sent()) {
        return;
    }

    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

function start_secure_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    send_security_headers();

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $params = session_get_cookie_params();

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params(array(
            'lifetime' => 0,
            'path' => isset($params['path']) ? $params['path'] : '/',
            'domain' => isset($params['domain']) ? $params['domain'] : '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ));
    } else {
        session_set_cookie_params(
            0,
            (isset($params['path']) ? $params['path'] : '/') . '; samesite=Lax',
            isset($params['domain']) ? $params['domain'] : '',
            $secure,
            true
        );
    }

    session_name('tripcalculator_session');
    session_start();

    if (empty($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
    }
}

function redirect($location)
{
    header('Location: ' . $location);
    exit;
}

function logout_current_user()
{
    start_secure_session();
    $_SESSION = array();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            isset($params['path']) ? $params['path'] : '/',
            isset($params['domain']) ? $params['domain'] : '',
            isset($params['secure']) ? $params['secure'] : false,
            true
        );
    }

    session_destroy();
}

function csrf_token()
{
    start_secure_session();

    if (empty($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
    }

    return $_SESSION['csrf_token'];
}

function csrf_input()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function require_post_csrf()
{
    start_secure_session();

    $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Invalid CSRF token.');
    }
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_post_request()
{
    return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
}

function post_string($key, $default = '')
{
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) {
        return $default;
    }

    return trim($_POST[$key]);
}

function post_raw_string($key, $default = '')
{
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) {
        return $default;
    }

    return $_POST[$key];
}

function post_legacy_hash($key)
{
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) {
        return null;
    }

    $value = trim($_POST[$key]);

    if (!is_legacy_password_value($value)) {
        return null;
    }

    return strtolower($value);
}

function get_string($key, $default = '')
{
    if (!isset($_GET[$key]) || !is_string($_GET[$key])) {
        return $default;
    }

    return trim($_GET[$key]);
}

function get_int($key)
{
    if (!isset($_GET[$key])) {
        return null;
    }

    $value = filter_var($_GET[$key], FILTER_VALIDATE_INT);
    if ($value === false) {
        return null;
    }

    return (int)$value;
}

function post_array_strings($key)
{
    if (!isset($_POST[$key]) || !is_array($_POST[$key])) {
        return array();
    }

    $values = array();
    foreach ($_POST[$key] as $value) {
        if (!is_string($value)) {
            continue;
        }

        $value = trim($value);
        if ($value === '') {
            continue;
        }

        $values[$value] = $value;
    }

    return array_values($values);
}

function post_boolean($key)
{
    if (!isset($_POST[$key])) {
        return false;
    }

    return in_array($_POST[$key], array('1', 'on', 'true'), true);
}

function session_array_get($key)
{
    start_secure_session();

    if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
        $_SESSION[$key] = array();
    }

    return $_SESSION[$key];
}

function normalize_label($value)
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    return ucfirst(strtolower($value));
}

function normalize_currency_label($value)
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    return strtoupper($value);
}

function prepare_note($value)
{
    return trim($value);
}

function normalize_price_input($value)
{
    if (is_int($value) || is_float($value)) {
        return number_format((float)$value, 2, '.', '');
    }

    if (!is_string($value)) {
        return null;
    }

    $normalized = trim($value);
    if ($normalized === '') {
        return null;
    }

    $normalized = preg_replace('/[\s\x{00A0}\x{2007}\x{202F}]+/u', '', $normalized);
    if (!is_string($normalized) || $normalized === '') {
        return null;
    }

    if (preg_match('/^[0-9.,]+$/', $normalized) !== 1) {
        return null;
    }

    $lastComma = strrpos($normalized, ',');
    $lastDot = strrpos($normalized, '.');
    $decimalPosition = false;

    if ($lastComma !== false || $lastDot !== false) {
        if ($lastComma === false) {
            $decimalPosition = $lastDot;
        } elseif ($lastDot === false) {
            $decimalPosition = $lastComma;
        } else {
            $decimalPosition = max($lastComma, $lastDot);
        }
    }

    if ($decimalPosition !== false) {
        $integerPart = substr($normalized, 0, $decimalPosition);
        $fractionPart = substr($normalized, $decimalPosition + 1);

        $integerPart = str_replace(array(',', '.'), '', $integerPart);
        $fractionPart = str_replace(array(',', '.'), '', $fractionPart);

        if ($integerPart === '') {
            $integerPart = '0';
        }

        $normalized = $fractionPart === ''
            ? $integerPart
            : $integerPart . '.' . $fractionPart;
    } else {
        $normalized = str_replace(array(',', '.'), '', $normalized);
    }

    if (preg_match('/^\d+(?:\.\d+)?$/', $normalized) !== 1) {
        return null;
    }

    return number_format((float)$normalized, 2, '.', '');
}

function is_valid_length($value, $max, $min = 1)
{
    $length = strlen($value);
    return $length >= $min && $length <= $max;
}

function query($conn, $sql, $params = array())
{
    $statement = $conn->prepare($sql);
    $statement->execute($params);
    return $statement;
}

function select($conn, $sql, $params = array())
{
    return query($conn, $sql, $params)->fetchAll();
}

function insert($conn, $sql, $params = array())
{
    query($conn, $sql, $params);
    return $conn->lastInsertId();
}

function update($conn, $sql, $params = array())
{
    return query($conn, $sql, $params)->rowCount();
}

function delete($conn, $sql, $params = array())
{
    return query($conn, $sql, $params)->rowCount();
}

function get_user_by_name($conn, $username)
{
    $rows = select(
        $conn,
        'SELECT `name`, `password` FROM `user` WHERE `name` = :name LIMIT 1',
        array(':name' => $username)
    );

    return isset($rows[0]) ? $rows[0] : null;
}

function migrate_all_legacy_password_rows($conn)
{
    static $alreadyMigrated = false;

    if ($alreadyMigrated) {
        return;
    }

    $alreadyMigrated = true;

    $rows = select($conn, 'SELECT `name`, `password` FROM `user`');

    foreach ($rows as $row) {
        if (!isset($row['name'], $row['password']) || !is_legacy_password_value($row['password'])) {
            continue;
        }

        update(
            $conn,
            'UPDATE `user` SET `password` = :password WHERE `name` = :name AND `password` = :current_password',
            array(
                ':password' => password_hash(strtolower($row['password']), PASSWORD_DEFAULT),
                ':name' => $row['name'],
                ':current_password' => $row['password'],
            )
        );
    }
}

function is_password_hash_value($value)
{
    $info = password_get_info($value);
    return !empty($info['algo']);
}

function is_legacy_password_value($value)
{
    return is_string($value) && preg_match('/^[a-f0-9]{128}$/i', $value) === 1;
}

function add_password_candidate(&$candidates, $value)
{
    if (!is_string($value) || $value === '') {
        return;
    }

    $candidates[$value] = $value;
}

function password_candidates($password, $legacyPassword = null)
{
    $candidates = array();

    if (!is_string($password) || $password === '') {
        return $candidates;
    }

    add_password_candidate($candidates, $password);

    if (is_legacy_password_value($password)) {
        add_password_candidate($candidates, strtolower($password));
    }

    if (is_string($legacyPassword) && is_legacy_password_value($legacyPassword)) {
        add_password_candidate($candidates, strtolower($legacyPassword));
    }

    return array_values($candidates);
}

function verify_stored_password($password, $storedPassword, $legacyPassword = null)
{
    if (!is_string($storedPassword) || $storedPassword === '') {
        return false;
    }

    $candidates = password_candidates($password, $legacyPassword);
    if (count($candidates) === 0) {
        return false;
    }

    if (is_password_hash_value($storedPassword)) {
        foreach ($candidates as $candidate) {
            if (password_verify($candidate, $storedPassword)) {
                return true;
            }
        }

        return false;
    }

    if (!is_legacy_password_value($storedPassword)) {
        return false;
    }

    foreach ($candidates as $candidate) {
        if (hash_equals(strtolower($storedPassword), $candidate)) {
            return true;
        }
    }

    return false;
}

function get_verified_password_candidate($password, $storedPassword, $legacyPassword = null)
{
    if (!is_string($storedPassword) || $storedPassword === '') {
        return null;
    }

    foreach (password_candidates($password, $legacyPassword) as $candidate) {
        if (is_password_hash_value($storedPassword)) {
            if (password_verify($candidate, $storedPassword)) {
                return $candidate;
            }
        } elseif (is_legacy_password_value($storedPassword) && hash_equals(strtolower($storedPassword), $candidate)) {
            return $candidate;
        }
    }

    return null;
}

function migrate_password_hash_if_needed($conn, $username, $password, $storedPassword, $legacyPassword = null)
{
    $candidate = null;

    if (is_password_hash_value($storedPassword)) {
        if (!password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            return;
        }

        $candidate = get_verified_password_candidate($password, $storedPassword, $legacyPassword);
    } elseif (is_legacy_password_value($storedPassword)) {
        $candidate = strtolower($storedPassword);
    }

    if ($candidate === null) {
        return;
    }

    update(
        $conn,
        'UPDATE `user` SET `password` = :password WHERE `name` = :name',
        array(
            ':password' => password_hash($candidate, PASSWORD_DEFAULT),
            ':name' => $username,
        )
    );
}

function authenticate_user($conn, $username, $password, $legacyPassword = null)
{
    if (!is_string($username) || !is_string($password) || $username === '' || $password === '') {
        return false;
    }

    $user = get_user_by_name($conn, $username);
    if ($user === null || !verify_stored_password($password, $user['password'], $legacyPassword)) {
        error_log('Failed login for user: ' . $username . ' from IP: ' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown'));
        return false;
    }

    migrate_password_hash_if_needed($conn, $username, $password, $user['password'], $legacyPassword);
    error_log('Successful login for user: ' . $username . ' from IP: ' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown'));
    return true;
}

function login_user($conn, $username, $password, $legacyPassword = null)
{
    if (!authenticate_user($conn, $username, $password, $legacyPassword)) {
        return false;
    }

    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['username'] = $username;
    unset($_SESSION['csrf_token']);
    csrf_token();

    return true;
}

function get_logged_in_username($conn)
{
    start_secure_session();

    if (!isset($_SESSION['username']) || !is_string($_SESSION['username']) || $_SESSION['username'] === '') {
        return null;
    }

    return get_user_by_name($conn, $_SESSION['username']) !== null ? $_SESSION['username'] : null;
}

function require_login($conn)
{
    $username = get_logged_in_username($conn);
    if ($username === null) {
        logout_current_user();
        redirect('index.php');
    }

    return $username;
}

function is_loged_in($conn, $username = null, $password = null, $legacyPassword = null)
{
    if ($username === null && $password === null) {
        return get_logged_in_username($conn) !== null;
    }

    return authenticate_user($conn, $username, $password, $legacyPassword);
}

function is_admin_user($username)
{
    $configs = get_config();
    return is_string($username) && isset($configs['admin']) && $configs['admin'] === $username;
}

function user_is_taken($conn, $username)
{
    if (!is_valid_length($username, 100)) {
        return false;
    }

    $rows = select(
        $conn,
        'SELECT `name` FROM `user` WHERE `name` = :name LIMIT 1',
        array(':name' => $username)
    );

    return (bool)count($rows);
}

function register($conn, $username, $password)
{
    if (!is_valid_length($username, 100) || !is_string($password) || $password === '') {
        return false;
    }

    insert(
        $conn,
        'INSERT INTO `user` (`name`, `password`) VALUES (:name, :password)',
        array(
            ':name' => $username,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
        )
    );

    return true;
}

function find_smallest($array)
{
    foreach ($array as $key => $item) {
        if ($item == '') {
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

function all_have_same_number($array)
{
    foreach ($array as $item) {
        if (!isset($number)) {
            $number = $item;
        } else {
            if ($item == 0) {
                if ($number == 0 || abs(($item - $number) / $number) > 0.01) {
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

function get_item($conn, $id)
{
    $rows = select(
        $conn,
        'SELECT * FROM `item` WHERE `id` = :id LIMIT 1',
        array(':id' => $id)
    );

    return isset($rows[0]) ? $rows[0] : null;
}

function get_item_set_id_for_item($conn, $itemId)
{
    $item = get_item($conn, $itemId);
    return $item !== null ? (int)$item['item_set_id'] : null;
}

function item_belongs_to_item_set($conn, $itemId, $itemSetId)
{
    if ($itemId === null || $itemSetId === null) {
        return false;
    }

    $rows = select(
        $conn,
        'SELECT `id` FROM `item` WHERE `id` = :item_id AND `item_set_id` = :item_set_id LIMIT 1',
        array(
            ':item_id' => $itemId,
            ':item_set_id' => $itemSetId,
        )
    );

    return (bool)count($rows);
}

function count_users_on_item($conn, $id)
{
    $rows = select(
        $conn,
        'SELECT COUNT(*) AS `count` FROM `item_has_user` WHERE `item_id` = :id',
        array(':id' => $id)
    );

    return isset($rows[0]) ? (int)$rows[0]['count'] : 0;
}

function price_per_item_for_one_person($conn, $id, $price)
{
    $count = count_users_on_item($conn, $id);
    if ($count === 0) {
        return 0;
    }

    return ((float)$price / $count);
}

function get_my_price_per_category($conn, $item_set_id, $user, $category, $currency)
{
    return select(
        $conn,
        'SELECT `item`.`price`, `item`.`id`
         FROM `item_set`
         INNER JOIN `item` ON `item_set`.`id` = `item`.`item_set_id`
         INNER JOIN `item_has_user` ON `item_has_user`.`item_id` = `item`.`id`
         WHERE `item_set`.`id` = :item_set_id
           AND `item_has_user`.`user_name` = :user
           AND `item`.`category_name` = :category
           AND `item`.`currency_name` = :currency',
        array(
            ':item_set_id' => $item_set_id,
            ':user' => $user,
            ':category' => $category,
            ':currency' => $currency,
        )
    );
}

function user_spent_after_calculation($conn, $id, $currency, $name)
{
    $sum = 0;
    $items = select(
        $conn,
        'SELECT `item`.`id`, `item`.`price`
         FROM `item_set`
         INNER JOIN `item` ON `item_set`.`id` = `item`.`item_set_id`
         INNER JOIN `item_has_user` ON `item_has_user`.`item_id` = `item`.`id`
         WHERE `item_set`.`id` = :item_set_id
           AND `item_has_user`.`user_name` = :name
           AND `item`.`currency_name` = :currency',
        array(
            ':item_set_id' => $id,
            ':name' => $name,
            ':currency' => $currency,
        )
    );

    foreach ($items as $item) {
        $count = count_users_on_item($conn, $item['id']);
        if ($count > 0) {
            $sum += ((float)$item['price'] / $count);
        }
    }

    return $sum;
}

function normalize_item_payload($conn, $array)
{
    $itemSetId = isset($array['id']) ? (int)$array['id'] : null;
    $payer = isset($array['user']) ? trim($array['user']) : '';
    $category = isset($array['category2']) && $array['category2'] !== '' ? trim($array['category2']) : (isset($array['category']) ? trim($array['category']) : '');
    $currency = isset($array['currency2']) && $array['currency2'] !== '' ? trim($array['currency2']) : (isset($array['currency']) ? trim($array['currency']) : '');
    $note = isset($array['note']) ? prepare_note($array['note']) : '';
    $price = isset($array['price']) ? normalize_price_input($array['price']) : null;

    if ($itemSetId === null || $price === null || (float)$price < 0) {
        return null;
    }

    if (!is_valid_length($payer, 100) || !is_valid_length($category, 100) || !is_valid_length($currency, 20) || strlen($note) > 255) {
        return null;
    }

    $allowedPayers = array_flip(array_map(function ($row) {
        return $row['name'];
    }, get_posible_payers($conn, $itemSetId)));

    if (!isset($allowedPayers[$payer])) {
        return null;
    }

    return array(
        'id' => $itemSetId,
        'user' => $payer,
        'category' => $category,
        'currency' => $currency,
        'note' => $note,
        'price' => $price,
    );
}

function insert_item_record($conn, $payload)
{
    update_category_for_item($conn, $payload['category']);
    update_currency_for_item($conn, $payload['currency']);

    return insert(
        $conn,
        'INSERT INTO `item` (`price`, `note`, `category_name`, `payer`, `item_set_id`, `currency_name`)
         VALUES (:price, :note, :category, :payer, :item_set_id, :currency)',
        array(
            ':price' => $payload['price'],
            ':note' => $payload['note'],
            ':category' => $payload['category'],
            ':payer' => $payload['user'],
            ':item_set_id' => $payload['id'],
            ':currency' => $payload['currency'],
        )
    );
}

function add_item($conn, $array)
{
    $payload = normalize_item_payload($conn, $array);
    if ($payload === null) {
        return null;
    }

    $conn->beginTransaction();

    try {
        $itemId = insert_item_record($conn, $payload);

        $conn->commit();
        return $itemId;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        throw $e;
    }
}

function get_pending_item_draft($itemSetId)
{
    $drafts = session_array_get('pending_item_drafts');

    if (!isset($drafts[$itemSetId]) || !is_array($drafts[$itemSetId])) {
        return null;
    }

    return $drafts[$itemSetId];
}

function set_pending_item_draft($itemSetId, $draft)
{
    start_secure_session();

    if (!isset($_SESSION['pending_item_drafts']) || !is_array($_SESSION['pending_item_drafts'])) {
        $_SESSION['pending_item_drafts'] = array();
    }

    $_SESSION['pending_item_drafts'][$itemSetId] = $draft;
}

function clear_pending_item_draft($itemSetId)
{
    start_secure_session();

    if (isset($_SESSION['pending_item_drafts'][$itemSetId])) {
        unset($_SESSION['pending_item_drafts'][$itemSetId]);
    }
}

function create_item_with_users($conn, $payload, $users)
{
    $normalizedPayload = normalize_item_payload($conn, $payload);
    if ($normalizedPayload === null) {
        return null;
    }

    $allowedUsers = array_flip(array_map(function ($row) {
        return $row['name'];
    }, get_posible_payers($conn, $normalizedPayload['id'])));

    $filteredUsers = array();
    foreach ($users as $user) {
        if (isset($allowedUsers[$user])) {
            $filteredUsers[$user] = $user;
        }
    }

    if (count($filteredUsers) === 0) {
        return null;
    }

    $conn->beginTransaction();

    try {
        $itemId = insert_item_record($conn, $normalizedPayload);
        add_users_to_item($conn, $itemId, array_values($filteredUsers));
        $conn->commit();

        return $itemId;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        throw $e;
    }
}

function get_category($conn)
{
    return select($conn, 'SELECT `name` FROM `category` ORDER BY `name` ASC');
}

function strip_category_currency($array)
{
    $new_array = array();
    foreach ($array as $item) {
        $new_array[] = $item['name'];
    }
    return $new_array;
}

function add_item_set($conn, $name, $owner)
{
    if (!is_valid_length($name, 100) || !is_valid_length($owner, 100)) {
        return null;
    }

    $conn->beginTransaction();

    try {
        $id = insert(
            $conn,
            'INSERT INTO `item_set` (`name`, `owner`) VALUES (:name, :owner)',
            array(
                ':name' => $name,
                ':owner' => $owner,
            )
        );

        insert(
            $conn,
            'INSERT INTO `user_has_item_set` (`item_set_id`, `user_name`) VALUES (:item_set_id, :user_name)',
            array(
                ':item_set_id' => $id,
                ':user_name' => $owner,
            )
        );

        $conn->commit();
        return $id;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        throw $e;
    }
}

function cant_see_itemset($conn, $id, $username)
{
    if ($id === null || !is_string($username) || $username === '') {
        return true;
    }

    if (is_admin_user($username)) {
        return false;
    }

    $rows = select(
        $conn,
        'SELECT `user_name`
         FROM `user_has_item_set`
         WHERE `item_set_id` = :item_set_id
           AND `user_name` = :user_name
         LIMIT 1',
        array(
            ':item_set_id' => $id,
            ':user_name' => $username,
        )
    );

    return !(bool)count($rows);
}

function user_has_item_set($conn, $id, $user)
{
    if (!is_valid_length($user, 100)) {
        return null;
    }

    query(
        $conn,
        'INSERT IGNORE INTO `user_has_item_set` (`item_set_id`, `user_name`) VALUES (:item_set_id, :user_name)',
        array(
            ':item_set_id' => $id,
            ':user_name' => $user,
        )
    );

    return true;
}

function get_currency($conn)
{
    return select($conn, 'SELECT `name` FROM `currency` ORDER BY `name` ASC');
}

function get_categorys_for_item_set($conn, $id)
{
    return select(
        $conn,
        'SELECT `item`.`category_name` AS `name`
         FROM `item`
         WHERE `item`.`item_set_id` = :item_set_id
         GROUP BY `item`.`category_name`
         ORDER BY `item`.`category_name` ASC',
        array(':item_set_id' => $id)
    );
}

function get_posible_payers($conn, $id)
{
    return select(
        $conn,
        'SELECT `user_has_item_set`.`user_name` AS `name`
         FROM `user_has_item_set`
         WHERE `user_has_item_set`.`item_set_id` = :item_set_id
         ORDER BY `user_has_item_set`.`user_name` ASC',
        array(':item_set_id' => $id)
    );
}

function add_users_to_item($conn, $id, $users)
{
    $itemSetId = get_item_set_id_for_item($conn, $id);
    if ($itemSetId === null) {
        return;
    }

    $allowedUsers = array_flip(array_map(function ($row) {
        return $row['name'];
    }, get_posible_payers($conn, $itemSetId)));

    foreach ($users as $user) {
        if (!isset($allowedUsers[$user])) {
            continue;
        }

        query(
            $conn,
            'INSERT IGNORE INTO `item_has_user` (`item_id`, `user_name`) VALUES (:item_id, :user_name)',
            array(
                ':item_id' => $id,
                ':user_name' => $user,
            )
        );
    }
}

function get_payer_for_item($conn, $id)
{
    $rows = select(
        $conn,
        'SELECT `payer` FROM `item` WHERE `id` = :id LIMIT 1',
        array(':id' => $id)
    );

    return isset($rows[0]) ? $rows[0]['payer'] : null;
}

function update_item($conn, $array)
{
    $itemId = isset($array['id']) ? (int)$array['id'] : null;
    $payer = isset($array['payer']) ? trim($array['payer']) : '';
    $category = isset($array['category']) ? trim($array['category']) : '';
    $currency = isset($array['currency']) ? trim($array['currency']) : '';
    $note = isset($array['note']) ? prepare_note($array['note']) : '';
    $price = isset($array['price']) ? normalize_price_input($array['price']) : null;
    $users = isset($array['users']) && is_array($array['users']) ? $array['users'] : array();

    if ($itemId === null || $price === null || (float)$price < 0) {
        return false;
    }

    $item = get_item($conn, $itemId);
    if ($item === null) {
        return false;
    }

    $allowedUsers = array_flip(array_map(function ($row) {
        return $row['name'];
    }, get_posible_payers($conn, $item['item_set_id'])));

    if (!isset($allowedUsers[$payer])) {
        return false;
    }

    $filteredUsers = array();
    foreach ($users as $user) {
        if (isset($allowedUsers[$user])) {
            $filteredUsers[] = $user;
        }
    }

    if (!is_valid_length($category, 100) || !is_valid_length($currency, 20) || strlen($note) > 255) {
        return false;
    }

    $conn->beginTransaction();

    try {
        delete(
            $conn,
            'DELETE FROM `item_has_user` WHERE `item_id` = :item_id',
            array(':item_id' => $itemId)
        );

        add_users_to_item($conn, $itemId, $filteredUsers);
        update_category_for_item($conn, $category);
        update_currency_for_item($conn, $currency);

        update(
            $conn,
            'UPDATE `item`
             SET `price` = :price,
                 `payer` = :payer,
                 `note` = :note,
                 `category_name` = :category,
                 `currency_name` = :currency
             WHERE `id` = :id',
            array(
                ':price' => $price,
                ':payer' => $payer,
                ':note' => $note,
                ':category' => $category,
                ':currency' => $currency,
                ':id' => $itemId,
            )
        );

        $conn->commit();
        return true;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        throw $e;
    }
}

function update_category_for_item($conn, $category)
{
    $rows = select(
        $conn,
        'SELECT `name` FROM `category` WHERE `name` = :name LIMIT 1',
        array(':name' => $category)
    );

    if (!(bool)count($rows)) {
        insert(
            $conn,
            'INSERT INTO `category` (`name`) VALUES (:name)',
            array(':name' => $category)
        );
    }
}

function update_currency_for_item($conn, $currency)
{
    $rows = select(
        $conn,
        'SELECT `name` FROM `currency` WHERE `name` = :name LIMIT 1',
        array(':name' => $currency)
    );

    if (!(bool)count($rows)) {
        insert(
            $conn,
            'INSERT INTO `currency` (`name`) VALUES (:name)',
            array(':name' => $currency)
        );
    }
}

function delete_item($conn, $id)
{
    $conn->beginTransaction();

    try {
        delete(
            $conn,
            'DELETE FROM `item_has_user` WHERE `item_id` = :item_id',
            array(':item_id' => $id)
        );

        delete(
            $conn,
            'DELETE FROM `item` WHERE `id` = :id',
            array(':id' => $id)
        );

        $conn->commit();
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        throw $e;
    }
}

function own_item_set($conn, $id, $name)
{
    if ($id === null || !is_string($name) || $name === '') {
        return false;
    }

    if (is_admin_user($name)) {
        return true;
    }

    $rows = select(
        $conn,
        'SELECT `owner` FROM `item_set` WHERE `id` = :id AND `owner` = :owner LIMIT 1',
        array(
            ':id' => $id,
            ':owner' => $name,
        )
    );

    return (bool)count($rows);
}

function is_editor($conn, $item_set_id, $name)
{
    if ($item_set_id === null || !is_string($name) || $name === '') {
        return false;
    }

    if (is_admin_user($name)) {
        return true;
    }

    $rows = select(
        $conn,
        'SELECT `user_name` FROM `editors` WHERE `item_set_id` = :item_set_id AND `user_name` = :user_name LIMIT 1',
        array(
            ':item_set_id' => $item_set_id,
            ':user_name' => $name,
        )
    );

    return (bool)count($rows);
}

function get_owner_of_item_set($conn, $id)
{
    $rows = select(
        $conn,
        'SELECT `owner` FROM `item_set` WHERE `id` = :id LIMIT 1',
        array(':id' => $id)
    );

    return isset($rows[0]) ? $rows[0]['owner'] : null;
}

function add_edditor($conn, $item_set_id, $username)
{
    if (!is_valid_length($username, 100)) {
        return null;
    }

    query(
        $conn,
        'INSERT IGNORE INTO `editors` (`item_set_id`, `user_name`) VALUES (:item_set_id, :user_name)',
        array(
            ':item_set_id' => $item_set_id,
            ':user_name' => $username,
        )
    );

    return true;
}

function get_item_set($conn, $id)
{
    $rows = select(
        $conn,
        'SELECT * FROM `item_set` WHERE `id` = :id LIMIT 1',
        array(':id' => $id)
    );

    return isset($rows[0]) ? $rows[0] : null;
}

function users_in_item_set_no_admin($conn, $id)
{
    return select(
        $conn,
        'SELECT `user`.`name`
         FROM `user`
         INNER JOIN `user_has_item_set` ON `user_has_item_set`.`user_name` = `user`.`name`
         INNER JOIN `item_set` ON `item_set`.`id` = `user_has_item_set`.`item_set_id`
         WHERE `item_set`.`id` = :item_set_id
           AND `user`.`name` != `item_set`.`owner`
         ORDER BY `user`.`name` ASC',
        array(':item_set_id' => $id)
    );
}

function update_item_set($conn, $id, $name)
{
    if (!is_valid_length($name, 100)) {
        return false;
    }

    update(
        $conn,
        'UPDATE `item_set` SET `name` = :name WHERE `id` = :id',
        array(
            ':name' => $name,
            ':id' => $id,
        )
    );

    return true;
}

function delete_editors($conn, $item_set_id, $delete)
{
    if (count($delete) === 0) {
        return 0;
    }

    $placeholders = implode(', ', array_fill(0, count($delete), '?'));
    $params = array_merge(array($item_set_id), array_values($delete));

    return delete(
        $conn,
        'DELETE FROM `editors` WHERE `item_set_id` = ? AND `user_name` IN (' . $placeholders . ')',
        $params
    );
}

function add_editors($conn, $item_set_id, $add)
{
    foreach ($add as $username) {
        add_edditor($conn, $item_set_id, $username);
    }
}

function get_editors($conn, $item_set_id)
{
    $rows = select(
        $conn,
        'SELECT `user_name` FROM `editors` WHERE `item_set_id` = :item_set_id',
        array(':item_set_id' => $item_set_id)
    );

    $editors = array();
    foreach ($rows as $row) {
        $editors[$row['user_name']] = true;
    }

    return $editors;
}

function get_editors_with_owner($conn, $item_set_id)
{
    $editors = get_editors($conn, $item_set_id);
    $owner = get_owner_of_item_set($conn, $item_set_id);

    if ($owner !== null) {
        $editors[$owner] = true;
    }

    return $editors;
}

function is_edditor_or_owner($conn, $item_set_id, $user)
{
    if (own_item_set($conn, $item_set_id, $user)) {
        return true;
    }

    $editors = get_editors($conn, $item_set_id);
    return isset($editors[$user]);
}

function change_password($conn, $username, $oldPass, $newPass, $oldLegacyPassword = null)
{
    $user = get_user_by_name($conn, $username);
    if ($user === null || !verify_stored_password($oldPass, $user['password'], $oldLegacyPassword)) {
        return false;
    }

    update(
        $conn,
        'UPDATE `user` SET `password` = :password WHERE `name` = :name',
        array(
            ':password' => password_hash($newPass, PASSWORD_DEFAULT),
            ':name' => $username,
        )
    );

    return true;
}
