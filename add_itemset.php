<?php

$pageTitle = 'Split Calculator | Add Trip';

include __DIR__ . '/functions.php';

$conn = connect_db();
start_secure_session();

$username = require_login($conn);
$error = '';
$tripName = '';

if (is_post_request()) {
    require_post_csrf();

    $name = post_string('name');
    $tripName = $name;

    if (!is_valid_length($name, 100)) {
        $error = 'Trip name must be between 1 and 100 characters.';
    } else {
        $id = add_item_set($conn, $name, $username);
        redirect('view.php?id=' . (int)$id);
    }
}

$navbarItems = '
    <li>
        <a href="add_itemset.php">Add Trip</a>
    </li>
    <li>
        <a href="edit_account.php">Edit Account</a>
    </li>
    <li>
        <a href="logout.php">Logout</a>
    </li>';

include __DIR__ . '/template.php';
?>

<main>
    <div class="container container--flow">
        <div class="center flow-card">
            <div class="flow-card__header">
                <h2>Add trip</h2>
                <p class="flow-card__intro">Create a new trip and start adding shared expenses to it.</p>
            </div>
            <div class="flow-card__body">
                <?php if ($error !== ''): ?>
                    <p class="form-message"><?php echo e($error); ?></p>
                <?php endif; ?>

                <form method="POST" action="" class="flow-form">
                    <?php echo csrf_input(); ?>
                    <div class="txt_field">
                        <input type="text" id="trip-name" name="name" placeholder=" " value="<?php echo e($tripName); ?>" maxlength="100" required>
                        <span></span>
                        <label for="trip-name">Trip name</label>
                    </div>
                    <div class="flow-actions flow-actions--single">
                        <input type="submit" name="submit" value="Add Trip">
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>

</html>
