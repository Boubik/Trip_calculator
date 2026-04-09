<?php

$pageTitle = 'Split Calculator | Add User';

include __DIR__ . '/functions.php';

$conn = connect_db();
start_secure_session();

$username = require_login($conn);
$itemSetId = get_int('id');

if (
    $itemSetId === null ||
    cant_see_itemset($conn, $itemSetId, $username) ||
    !is_edditor_or_owner($conn, $itemSetId, $username)
) {
    redirect('index.php');
}

$error = '';
$usernameToAdd = '';
$makeEditor = false;
$canManageEditors = own_item_set($conn, $itemSetId, $username);

if (is_post_request()) {
    require_post_csrf();

    $usernameToAdd = post_string('username');
    $makeEditor = post_boolean('editor');

    if (!user_is_taken($conn, $usernameToAdd)) {
        $error = 'User does not exist.';
    } else {
        user_has_item_set($conn, $itemSetId, $usernameToAdd);

        if ($canManageEditors && $makeEditor) {
            add_edditor($conn, $itemSetId, $usernameToAdd);
        }

        redirect('view.php?id=' . (int)$itemSetId);
    }
}

$navbarItems = '
<li>
    <a href="view.php?id=' . (int)$itemSetId . '">Back</a>
</li>
<li>
    <a href="edit_account.php">Edit Account</a>
</li>
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
                <h2>Add person</h2>
                <p class="flow-card__intro">Invite another existing user to this trip.</p>
            </div>
            <div class="flow-card__body">
                <?php if ($error !== ''): ?>
                    <p class="form-message"><?php echo e($error); ?></p>
                <?php endif; ?>

                <form method="POST" action="" class="flow-form">
                    <?php echo csrf_input(); ?>

                    <div class="txt_field">
                        <input type="text" id="username" name="username" placeholder=" " value="<?php echo e($usernameToAdd); ?>" maxlength="100" required>
                        <span></span>
                        <label for="username">Username</label>
                    </div>

                    <?php if ($canManageEditors): ?>
                        <label class="toggle-option">
                            <input type="checkbox" name="editor" value="1" <?php echo $makeEditor ? 'checked' : ''; ?>>
                            <span class="toggle-option__content">
                                <span class="toggle-option__title">Give editor access</span>
                                <span class="toggle-option__hint">Editors can modify items and manage the trip content.</span>
                            </span>
                        </label>
                    <?php endif; ?>

                    <div class="flow-actions flow-actions--single">
                        <input type="submit" name="submit" value="Add Person">
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>

</html>
