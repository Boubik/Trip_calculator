<?php

$pageTitle = 'Register | Split Calculator';

include __DIR__ . '/functions.php';

$conn = connect_db();
start_secure_session();

if (get_logged_in_username($conn) !== null) {
    redirect('index.php');
}

$errors = array();

if (is_post_request()) {
    require_post_csrf();

    $username = post_string('username');
    $password1 = post_raw_string('password1');
    $password2 = post_raw_string('password2');

    if (!is_valid_length($username, 100)) {
        $errors[] = 'Username must be between 1 and 100 characters.';
    } elseif (user_is_taken($conn, $username)) {
        $errors[] = 'Username is already taken.';
    }

    if (strlen($password1) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password1 !== $password2) {
        $errors[] = 'Passwords must match.';
    }

    if (count($errors) === 0) {
        register($conn, $username, $password1);
        login_user($conn, $username, $password1);
        redirect('index.php');
    }
}

include __DIR__ . '/template.php';
?>

<main>
    <div class="container">
        <div class="center">
            <h1>Register</h1>

            <?php foreach ($errors as $error): ?>
                <p><?php echo e($error); ?></p>
            <?php endforeach; ?>

            <form method="POST" action="">
                <?php echo csrf_input(); ?>

                <div class="txt_field">
                    <input type="text" id="username" name="username" placeholder="Username" value="">
                    <span></span>
                    <label>Username</label>
                </div>

                <div class="txt_field">
                    <input type="password" id="password1" name="password1" placeholder="Password" autocomplete="new-password">
                    <span></span>
                    <label>Password</label>
                </div>

                <div class="txt_field">
                    <input type="password" id="password2" name="password2" placeholder="Repeat password" autocomplete="new-password">
                    <span></span>
                    <label>Password</label>
                </div>

                <input type="submit" name="submit" value="Register">
                <div class="signup_link">
                    Already a member? <a href="index.php">Login</a>
                </div>
            </form>
        </div>
    </div>
</main>
</body>

</html>
