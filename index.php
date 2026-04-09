<?php

$pageTitle = 'Split Calculator | Trips';
$head = '<script src="js/sha3.js"></script>
<script src="js/changePasswordsIndex.js?v=20260409"></script>';

include __DIR__ . '/functions.php';

$conn = connect_db();
start_secure_session();

$loginError = '';

if (is_post_request()) {
    require_post_csrf();

    $usernameInput = post_string('username');
    $passwordInput = post_raw_string('password');
    $legacyPasswordInput = post_legacy_hash('legacy_password');

    if (login_user($conn, $usernameInput, $passwordInput, $legacyPasswordInput)) {
        redirect('index.php');
    }

    $loginError = 'Invalid username or password.';
}

$username = get_logged_in_username($conn);
$login = $username !== null;

if ($login) {
    $navbarItems = '
    <li>
        <a href="add_itemset.php">Add Trip</a>
    </li>
    <li>
        <a href="edit_account.php">Edit Account</a>
    </li>
    <li>
        <a href="logout.php">Logout</a>
    </li>
    ';
}

include __DIR__ . '/template.php';
?>

<main>
    <?php if ($login): ?>
        <?php
        $rows = select(
            $conn,
            'SELECT `item_set`.`id`, `item_set`.`name`
             FROM `item_set`
             INNER JOIN `user_has_item_set` ON `user_has_item_set`.`item_set_id` = `item_set`.`id`
             WHERE `user_has_item_set`.`user_name` = :user_name
             ORDER BY `item_set`.`date` DESC, `item_set`.`id` DESC',
            array(':user_name' => $username)
        );
        ?>
        <h1 class="heading">TRIPS</h1>
        <div class="trips-vypis">
            <?php if (count($rows) === 0): ?>
                <h2>No trips. Add one with:</h2>
                <a class="goodButton" href="add_itemset.php">Add Trip</a>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <a class="child" id="item" href="view.php?id=<?php echo (int)$row['id']; ?>">
                        <?php echo e($row['name']); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="center">
                <h1>Login</h1>
                <?php if ($loginError !== ''): ?>
                    <p><?php echo e($loginError); ?></p>
                <?php endif; ?>
                <form method="POST" action="" id="login-form">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" id="legacy_password" name="legacy_password" value="">
                    <div class="txt_field">
                        <input type="text" id="username" name="username" placeholder="Username" value="">
                        <span></span>
                        <label>Username</label>
                    </div>

                    <div class="txt_field">
                        <input type="password" id="password" name="password" placeholder="Password" autocomplete="current-password">
                        <span></span>
                        <label>Password</label>
                    </div>

                    <input type="submit" name="submit" value="Login">
                    <div class="signup_link">
                        Not a member? <a href="register.php">Sign-Up</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>
</body>

</html>
