<?php

include __DIR__ . '/functions.php';

$conn = connect_db();
start_secure_session();

$username = require_login($conn);
$itemSetId = get_int('id');

if ($itemSetId === null || !own_item_set($conn, $itemSetId, $username)) {
    redirect('index.php');
}

$itemSet = get_item_set($conn, $itemSetId);
if ($itemSet === null) {
    redirect('index.php');
}

$pageTitle = 'Split Calculator | Edit Trip';
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

$error = '';
$tripUsers = users_in_item_set_no_admin($conn, $itemSetId);
$selectedEditors = get_editors($conn, $itemSetId);
$tripName = $itemSet['name'];

if (is_post_request()) {
    require_post_csrf();

    $name = post_string('name');
    $tripName = $name;
    $selectedEditors = array_flip(post_array_strings('editor'));

    if (!is_valid_length($name, 100)) {
        $error = 'Trip name must be between 1 and 100 characters.';
    } else {
        if ($itemSet['name'] !== $name) {
            update_item_set($conn, $itemSetId, $name);
            $itemSet = get_item_set($conn, $itemSetId);
        }

        $currentEditors = get_editors($conn, $itemSetId);
        $delete = array();
        $add = array();

        foreach ($tripUsers as $row) {
            $nameKey = $row['name'];

            if (isset($selectedEditors[$nameKey])) {
                if (!isset($currentEditors[$nameKey])) {
                    $add[] = $nameKey;
                }
            } elseif (isset($currentEditors[$nameKey])) {
                $delete[] = $nameKey;
            }
        }

        if (count($delete) > 0) {
            delete_editors($conn, $itemSetId, $delete);
        }

        if (count($add) > 0) {
            add_editors($conn, $itemSetId, $add);
        }

        redirect('view.php?id=' . (int)$itemSetId);
    }
}

include __DIR__ . '/template.php';
?>

<main>
    <div class="container container--flow">
        <div class="center flow-card">
            <div class="flow-card__header">
                <h2>Edit trip</h2>
                <p class="flow-card__intro">Update the trip name and choose which members should have editor access.</p>
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

                    <div class="flow-field">
                        <label class="flow-label">Editors</label>

                        <?php if (count($tripUsers) === 0): ?>
                            <p class="flow-note">There are no other users in this trip yet. Add people first if you want to grant editor access.</p>
                        <?php else: ?>
                            <div class="participant-list">
                                <?php foreach ($tripUsers as $row): ?>
                                    <label class="participant-option">
                                        <input
                                            type="checkbox"
                                            name="editor[]"
                                            value="<?php echo e($row['name']); ?>"
                                            <?php echo isset($selectedEditors[$row['name']]) ? 'checked' : ''; ?>
                                        >
                                        <span><?php echo e($row['name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flow-actions flow-actions--single">
                        <input type="submit" name="submit" value="Save Changes">
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>

</html>
