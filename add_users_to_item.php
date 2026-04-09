<?php

$pageTitle = 'Split Calculator | Add Users To Item';

include __DIR__ . '/functions.php';

$conn = connect_db();
start_secure_session();

$username = require_login($conn);
$itemSetId = get_int('id');

if ($itemSetId === null || cant_see_itemset($conn, $itemSetId, $username)) {
    redirect('index.php');
}

$draft = get_pending_item_draft($itemSetId);

if (
    $draft === null ||
    !isset($draft['payload']) ||
    !is_array($draft['payload'])
) {
    redirect('add_item.php?id=' . (int)$itemSetId);
}

$error = '';
$selectedUsers = array($username => true);

if (is_post_request()) {
    require_post_csrf();

    $submitAction = post_string('submit');
    $selectedUsers = array_flip(post_array_strings('users'));

    if ($submitAction === 'Back') {
        redirect('add_item.php?id=' . (int)$itemSetId);
    }

    if ($submitAction === 'Cancel') {
        clear_pending_item_draft($itemSetId);
        redirect('view.php?id=' . (int)$itemSetId);
    }

    $itemId = create_item_with_users($conn, $draft['payload'], array_keys($selectedUsers));

    if ($itemId === null) {
        $error = 'Select at least one participant before saving the item.';
    } else {
        clear_pending_item_draft($itemSetId);
        redirect('view.php?id=' . (int)$itemSetId);
    }
}

$possiblePayers = get_posible_payers($conn, $itemSetId);
$payload = $draft['payload'];

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
        <div class="center flow-card participant-step">
            <div class="flow-card__header participant-step__header">
                <h2>The people that will pay it</h2>
                <p class="flow-card__intro participant-step__intro">Select everyone who should share this item.</p>
            </div>

            <div class="flow-card__body participant-step__body">
                <?php if ($error !== ''): ?>
                    <p class="form-message"><?php echo e($error); ?></p>
                <?php endif; ?>

                <div class="item-draft-summary">
                    <p>
                        <strong>Payer:</strong>
                        <?php echo e($payload['user']); ?>
                    </p>
                    <p>
                        <strong>Category:</strong>
                        <?php echo e($payload['category']); ?>
                    </p>
                    <p>
                        <strong>Price:</strong>
                        <?php echo number_format((float)$payload['price'], 2, ',', ' ') . ' ' . e($payload['currency']); ?>
                    </p>
                    <?php if ($payload['note'] !== ''): ?>
                        <p>
                            <strong>Note:</strong>
                            <?php echo e($payload['note']); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <form method="POST" action="" class="flow-form">
                    <?php echo csrf_input(); ?>

                    <div class="participant-list">
                        <?php foreach ($possiblePayers as $row): ?>
                            <label class="participant-option">
                                <input
                                    type="checkbox"
                                    name="users[]"
                                    value="<?php echo e($row['name']); ?>"
                                    <?php echo isset($selectedUsers[$row['name']]) ? 'checked' : ''; ?>
                                >
                                <span><?php echo e($row['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="flow-actions form-actions">
                        <input type="submit" name="submit" value="Add">
                        <input class="secondary-button" type="submit" name="submit" value="Back">
                        <input class="danger-button" type="submit" name="submit" value="Cancel">
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>

</html>
