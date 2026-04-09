<?php

include __DIR__ . '/functions.php';

$conn = connect_db();
start_secure_session();

$username = require_login($conn);
$itemSetId = get_int('back');
$itemId = get_int('id');
$deleteMode = isset($_GET['delete']);

if (
    $itemSetId === null ||
    $itemId === null ||
    !item_belongs_to_item_set($conn, $itemId, $itemSetId) ||
    !is_edditor_or_owner($conn, $itemSetId, $username)
) {
    redirect('index.php');
}

$item = get_item($conn, $itemId);
if ($item === null) {
    redirect('index.php');
}

$pageTitle = 'Split Calculator | Edit Item';
$head = '<script src="js/priceFormatter.js?v=20260409"></script>';
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

if (is_post_request()) {
    require_post_csrf();

    if ($deleteMode) {
        if (post_string('submit') === 'Delete') {
            delete_item($conn, $itemId);
        }

        redirect('view.php?id=' . (int)$itemSetId);
    }

    if (post_string('submit') === 'Cancel') {
        redirect('view.php?id=' . (int)$itemSetId);
    }

    $category = normalize_label(post_string('category'));
    $category2 = normalize_label(post_string('category2'));
    $currency = normalize_currency_label(post_string('currency'));
    $currency2 = normalize_currency_label(post_string('currency2'));

    $updatePayload = array(
        'id' => $itemId,
        'payer' => post_string('payer'),
        'users' => post_array_strings('users'),
        'category' => $category2 !== '' ? $category2 : $category,
        'price' => post_string('price'),
        'currency' => $currency2 !== '' ? $currency2 : $currency,
        'note' => prepare_note(post_string('note')),
    );

    if (!update_item($conn, $updatePayload)) {
        $error = 'Item contains invalid data.';
    } else {
        redirect('view.php?id=' . (int)$itemSetId);
    }
}

$possiblePayers = get_posible_payers($conn, $itemSetId);
$users = select(
    $conn,
    'SELECT `user_has_item_set`.`user_name`,
            EXISTS(
                SELECT 1
                FROM `item_has_user`
                WHERE `item_has_user`.`item_id` = :item_id
                  AND `item_has_user`.`user_name` = `user_has_item_set`.`user_name`
            ) AS `checked`
     FROM `user_has_item_set`
     WHERE `user_has_item_set`.`item_set_id` = :item_set_id
     ORDER BY `user_has_item_set`.`user_name` ASC',
    array(
        ':item_id' => $itemId,
        ':item_set_id' => $itemSetId,
    )
);
$categories = get_categorys_for_item_set($conn, $itemSetId);
$currencies = get_currency($conn);
$selectedUsers = array();

foreach ($users as $user) {
    if ((int)$user['checked'] === 1) {
        $selectedUsers[$user['user_name']] = true;
    }
}

$formData = array(
    'price' => $item['price'],
    'payer' => $item['payer'],
    'note' => $item['note'],
    'category' => $item['category_name'],
    'category2' => '',
    'currency' => $item['currency_name'],
    'currency2' => '',
);

if (is_post_request() && !$deleteMode && post_string('submit') !== 'Cancel') {
    $formData = array(
        'price' => post_string('price'),
        'payer' => post_string('payer'),
        'note' => post_string('note'),
        'category' => post_string('category'),
        'category2' => post_string('category2'),
        'currency' => post_string('currency'),
        'currency2' => post_string('currency2'),
    );
    $selectedUsers = array_flip(post_array_strings('users'));
}

include __DIR__ . '/template.php';
?>

<main>
    <div class="container container--flow">
        <div class="center flow-card">
            <?php if ($deleteMode): ?>
                <div class="flow-card__header">
                    <h2>Delete item</h2>
                    <p class="flow-card__intro">This will permanently remove the expense and all assigned participants.</p>
                </div>
                <div class="flow-card__body">
                    <div class="item-draft-summary">
                        <p>
                            <strong>Payer:</strong>
                            <?php echo e($item['payer']); ?>
                        </p>
                        <p>
                            <strong>Category:</strong>
                            <?php echo e($item['category_name']); ?>
                        </p>
                        <p>
                            <strong>Price:</strong>
                            <?php echo number_format((float)$item['price'], 2, ',', ' ') . ' ' . e($item['currency_name']); ?>
                        </p>
                    </div>

                    <form method="POST" action="" class="flow-form">
                        <?php echo csrf_input(); ?>
                        <div class="flow-actions">
                            <input class="danger-button" type="submit" name="submit" value="Delete">
                            <input class="secondary-button" type="submit" name="submit" value="Cancel">
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="flow-card__header">
                    <h2>Edit item</h2>
                    <p class="flow-card__intro">Update the expense details and participants who should share it.</p>
                </div>
                <div class="flow-card__body">
                    <?php if ($error !== ''): ?>
                        <p class="form-message"><?php echo e($error); ?></p>
                    <?php endif; ?>

                    <form method="POST" action="" class="flow-form">
                        <?php echo csrf_input(); ?>

                        <div class="flow-field">
                            <label class="flow-label" for="payer">Payer</label>
                            <div class="select-field">
                                <select id="payer" name="payer" required>
                                    <?php foreach ($possiblePayers as $row): ?>
                                        <option value="<?php echo e($row['name']); ?>"<?php echo $formData['payer'] === $row['name'] ? ' selected' : ''; ?>>
                                            <?php echo e($row['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="field-grid">
                            <div class="flow-field">
                                <label class="flow-label" for="category">Existing category</label>
                                <div class="select-field">
                                    <select id="category" name="category">
                                        <?php foreach ($categories as $row): ?>
                                            <option value="<?php echo e($row['name']); ?>"<?php echo $formData['category'] === $row['name'] ? ' selected' : ''; ?>>
                                                <?php echo e($row['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="txt_field">
                                <input type="text" id="category2" name="category2" placeholder=" " value="<?php echo e($formData['category2']); ?>" maxlength="100">
                                <span></span>
                                <label for="category2">New category</label>
                            </div>
                        </div>

                        <div class="field-grid">
                            <div class="flow-field">
                                <label class="flow-label" for="currency">Currency</label>
                                <div class="select-field">
                                    <select id="currency" name="currency">
                                        <?php foreach ($currencies as $row): ?>
                                            <option value="<?php echo e($row['name']); ?>"<?php echo $formData['currency'] === $row['name'] ? ' selected' : ''; ?>>
                                                <?php echo e($row['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="txt_field">
                                <input type="text" id="currency2" name="currency2" placeholder=" " value="<?php echo e($formData['currency2']); ?>" maxlength="20">
                                <span></span>
                                <label for="currency2">New currency</label>
                            </div>
                        </div>

                        <div class="txt_field">
                            <input type="text" id="price" name="price" placeholder="0,00" value="<?php echo e($formData['price']); ?>" inputmode="decimal" autocomplete="off" data-price-input="true" required>
                            <span></span>
                            <label for="price">Price</label>
                        </div>

                        <div class="txt_field">
                            <input type="text" id="note" name="note" placeholder="Additional info" value="<?php echo e($formData['note']); ?>" maxlength="255">
                            <span></span>
                            <label for="note">Note</label>
                        </div>

                        <div class="flow-field">
                            <label class="flow-label">Will pay</label>
                            <div class="participant-list">
                                <?php foreach ($users as $user): ?>
                                    <label class="participant-option">
                                        <input
                                            type="checkbox"
                                            name="users[]"
                                            value="<?php echo e($user['user_name']); ?>"
                                            <?php echo isset($selectedUsers[$user['user_name']]) ? 'checked' : ''; ?>
                                        >
                                        <span><?php echo e($user['user_name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="flow-actions">
                            <input type="submit" name="submit" value="Save Changes">
                            <input class="secondary-button" type="submit" name="submit" value="Cancel">
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>

</html>
