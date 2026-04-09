<?php

include __DIR__ . '/functions.php';

$conn = connect_db();
start_secure_session();

$username = require_login($conn);
$itemSetId = get_int('id');

if ($itemSetId === null || cant_see_itemset($conn, $itemSetId, $username)) {
    redirect('index.php');
}

$pageTitle = 'Split Calculator | Add Item';
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
$possiblePayers = get_posible_payers($conn, $itemSetId);
$categories = get_categorys_for_item_set($conn, $itemSetId);
$currencies = get_currency($conn);
$existingDraft = get_pending_item_draft($itemSetId);
$formData = array(
    'user' => '',
    'category' => '',
    'category2' => '',
    'price' => '',
    'currency' => '',
    'currency2' => '',
    'note' => '',
);

if ($existingDraft !== null && isset($existingDraft['form']) && is_array($existingDraft['form'])) {
    $formData = array_merge($formData, $existingDraft['form']);
}

if (is_post_request()) {
    require_post_csrf();

    $formData = array(
        'user' => post_string('user'),
        'category' => post_string('category'),
        'category2' => post_string('category2'),
        'price' => post_string('price'),
        'currency' => post_string('currency'),
        'currency2' => post_string('currency2'),
        'note' => post_string('note'),
    );

    $payload = normalize_item_payload($conn, array(
        'id' => $itemSetId,
        'user' => $formData['user'],
        'category' => normalize_label($formData['category']),
        'category2' => normalize_label($formData['category2']),
        'price' => $formData['price'],
        'currency' => normalize_currency_label($formData['currency']),
        'currency2' => normalize_currency_label($formData['currency2']),
        'note' => prepare_note($formData['note']),
    ));

    if ($payload === null) {
        $error = 'Item contains invalid data.';
    } else {
        set_pending_item_draft($itemSetId, array(
            'form' => $formData,
            'payload' => $payload,
        ));

        redirect('add_users_to_item.php?id=' . (int)$itemSetId);
    }
}

include __DIR__ . '/template.php';
?>

<main>
    <div class="container container--flow">
        <div class="center flow-card">
            <div class="flow-card__header">
                <h2>Add item</h2>
                <p class="flow-card__intro">Fill in the expense details. Participants are selected in the next step.</p>
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
                            <select id="payer" name="user" required>
                                <?php foreach ($possiblePayers as $index => $row): ?>
                                    <option
                                        value="<?php echo e($row['name']); ?>"
                                        <?php echo ($formData['user'] !== '' ? $formData['user'] === $row['name'] : $index === 0) ? 'selected' : ''; ?>
                                    >
                                        <?php echo e($row['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="flow-field">
                            <label class="flow-label" for="category">Existing category</label>
                            <?php if (count($categories) > 0): ?>
                                <div class="select-field">
                                    <select id="category" name="category">
                                        <?php foreach ($categories as $index => $row): ?>
                                            <option
                                                value="<?php echo e($row['name']); ?>"
                                                <?php echo ($formData['category'] !== '' ? $formData['category'] === $row['name'] : $index === 0) ? 'selected' : ''; ?>
                                            >
                                                <?php echo e($row['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <p class="flow-note">No category exists yet. Create one on the right.</p>
                            <?php endif; ?>
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
                                <select id="currency" name="currency" required>
                                    <?php foreach ($currencies as $index => $row): ?>
                                        <option
                                            value="<?php echo e($row['name']); ?>"
                                            <?php echo ($formData['currency'] !== '' ? $formData['currency'] === $row['name'] : $index === 0) ? 'selected' : ''; ?>
                                        >
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

                    <div class="flow-actions flow-actions--single">
                        <input type="submit" name="submit" value="Continue">
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>

</html>
