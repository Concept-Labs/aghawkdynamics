<?php

// Get block ID, parcel ID, and account ID from the URL
if (!isset($_GET['block_id']) || !isset($_SESSION['account_id'])) {
    die('Account ID and Block ID are required.');
}
$block_id = intval($_GET['block_id']);
$parcel_id = intval($_GET['parcel_id']);
$account_id = intval($_SESSION['account_id']);


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action']=='editBlock') {
    update_block_details($block_id, $_POST);
}


// Fetch block details
$sql_block = "SELECT * FROM Blocks WHERE block_id = ? AND  account_id = ?";
$stmt_block = $conn->prepare($sql_block);
$stmt_block->bind_param("ii", $block_id, $account_id);
$stmt_block->execute();
$result_block = $stmt_block->get_result();

if ($result_block->num_rows == 0) {
    die('Block not found.');
}
$block = $result_block->fetch_assoc();

// Fetch parcel details
$sql_parcel = "SELECT * FROM Parcels WHERE parcel_id = ? AND  account_id = ?";
$stmt_parcel = $conn->prepare($sql_parcel);
$stmt_parcel->bind_param("ii", $parcel_id, $account_id);
$stmt_parcel->execute();
$result_parcel = $stmt_parcel->get_result();

if ($result_parcel->num_rows == 0) {
    die('Parcel not found.');
}
$parcel = $result_parcel->fetch_assoc();
$parcelNickname = $parcel['nickname'];


// Close statements
$conn->close();



?>



<section class="adminSection">
    <h3>Edit Block</h3>
    <br />
    <h5><span style="font-weight:400;">Parcel:</span> <strong><?php echo htmlspecialchars($parcelNickname); ?></strong></h5>
    <form method="POST" action="block_detail?block_id=<?= $block['block_id']; ?>">
        <input type="hidden" name="action" value="editBlock" />
        <input type="hidden" name="block_id" value="<?= $block['block_id']; ?>" />
        <div class="formInlineFieldsWrap">
            <label>Block Nickname:</label>
            <input type="text" name="nickname" value="<?php echo htmlspecialchars($block['nickname']); ?>" required>
        </div>
        <div class="formInlineFieldsWrap">
            <label>Acres:</label>
            <input type="number" name="acres" step="0.01" value="<?php echo htmlspecialchars($block['acres']); ?>" required>
        </div>
        <div class="formInlineFieldsWrap">
            <label>Usage Type:</label>
            <select name="crop_category" required>
                <option value="">Select Usage Type</option>
                <option value="Orchard" <?php if ($block['crop_category'] == 'Orchard') echo 'selected'; ?>>Orchard</option>
                <option value="Vineyard" <?php if ($block['crop_category'] == 'Vineyard') echo 'selected'; ?>>Vineyard</option>
                <option value="Row Crops" <?php if ($block['crop_category'] == 'Row Crops') echo 'selected'; ?>>Row Crops</option>
                <option value="Pasture" <?php if ($block['crop_category'] == 'Pasture') echo 'selected'; ?>>Pasture</option>
                <option value="Grass field" <?php if ($block['crop_category'] == 'Grass field') echo 'selected'; ?>>Grass field</option>
                <option value="Mix" <?php if ($block['crop_category'] == 'Mix') echo 'selected'; ?>>Mix</option>
            </select>
        </div>
        <br class="Clear" />
        <div class="formInlineFieldsWrap">
            <label>Notes:</label>
            <textarea name="notes" rows="4"><?php echo htmlspecialchars($block['notes']); ?></textarea>
        </div>
        <p>
            <button type="submit">Save Changes</button> &nbsp;
            <a href="blocks">Cancel &amp; return to Blocks &raquo;</a>
        </p>
    </form>
</section>




