<?php

$pageTitle = 'Split Calculator | Edit Account';
$head = '<script src="js/sha3.js"></script>
<script src="js/changePasswords.js?v=20260409"></script>';

include __DIR__ . '/functions.php';

$conn = connect_db();
start_secure_session();

$username = require_login($conn);
$errors = array();

if (is_post_request()) {
    require_post_csrf();

    $oldPassword = post_raw_string('oldPassword');
    $newPassword = post_raw_string('newPassword');
    $newPassword2 = post_raw_string('newPassword2');
    $oldPasswordLegacy = post_legacy_hash('oldPasswordLegacy');

    if (!authenticate_user($conn, $username, $oldPassword, $oldPasswordLegacy)) {
        $errors[] = 'Old password is incorrect.';
    }

    if ($newPassword !== $newPassword2) {
        $errors[] = 'New passwords must match.';
    }

    if (strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }

    if (count($errors) === 0) {
        change_password($conn, $username, $oldPassword, $newPassword, $oldPasswordLegacy);
        logout_current_user();
        redirect('index.php');
    }
}

$navbarItems = '
<li>
    <a href="logout.php">Logout</a>
</li>
';

include __DIR__ . '/template.php';
?>

<main>
    <div class="container container--flow">
        <div class="center flow-card">
            <div class="flow-card__header">
                <h2>Edit account</h2>
                <p class="flow-card__intro">Change your password. After saving, you will be signed out and asked to log in again.</p>
            </div>
            <div class="flow-card__body">
                <?php foreach ($errors as $error): ?>
                    <p class="form-message"><?php echo e($error); ?></p>
                <?php endforeach; ?>

                <form method="POST" action="" class="flow-form">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" id="oldPasswordLegacy" name="oldPasswordLegacy" value="">

                    <div class="txt_field">
                        <input type="password" id="oldPassword" name="oldPassword" placeholder=" " value="" autocomplete="current-password">
                        <span></span>
                        <label for="oldPassword">Current password</label>
                    </div>

                    <div class="txt_field">
                        <input type="password" id="newPassword" name="newPassword" placeholder=" " value="" autocomplete="new-password">
                        <span></span>
                        <label for="newPassword">New password</label>
                    </div>

                    <div class="txt_field">
                        <input type="password" id="newPassword2" name="newPassword2" placeholder=" " value="" autocomplete="new-password">
                        <span></span>
                        <label for="newPassword2">Confirm new password</label>
                    </div>

                    <div class="flow-actions flow-actions--single">
                        <input type="submit" name="submit" value="Change Password">
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>

</html>
