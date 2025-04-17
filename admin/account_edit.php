<?php


// Get account ID from the URL
if (!isset($_GET['id'])) {
    die('Account ID is required.');
}
$account_id = intval($_GET['id']);


// Handle form submission by calling a function in _inc.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    update_account_details($account_id, $_POST);
}


// Fetch account details from the database
$sql_account = "SELECT * FROM Accounts WHERE account_id = ?";
$stmt_account = $conn->prepare($sql_account);
$stmt_account->bind_param("i", $account_id);
$stmt_account->execute();
$result_account = $stmt_account->get_result();

if ($result_account->num_rows == 0) {
    die('Account not found.');
}
$account = $result_account->fetch_assoc();
?>
<section class="adminSection" id="account-details">
        <h3>Edit Account: <span><?php echo htmlspecialchars($account['business_name']); ?></span></h3>
		
        <form method="POST">
            <table border="1">
                <tr>
                    <th><label>Business Name:</label></th>
                    <td><input type="text" name="business_name" value="<?php echo htmlspecialchars($account['business_name']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>Business Phone:</label></th>
                    <td><input type="text" name="business_phone" value="<?php echo htmlspecialchars($account['business_phone']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>Street Address:</label></th>
                    <td><input type="text" name="street_address" value="<?php echo htmlspecialchars($account['street_address']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>City:</label></th>
                    <td><input type="text" name="city" value="<?php echo htmlspecialchars($account['city']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>State:</label></th>
                    <td><input type="text" name="state" value="<?php echo htmlspecialchars($account['state']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>Zip Code:</label></th>
                    <td><input type="text" name="zip" value="<?php echo htmlspecialchars($account['zip']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>Acreage Size:</label></th>
                    <td>
                        <select name="acreage_size">
                            <option value="Under 50" <?php if ($account['acreage_size'] == 'Under 50') echo 'selected'; ?>>Under 50</option>
                            <option value="50-200" <?php if ($account['acreage_size'] == '50-200') echo 'selected'; ?>>50-200</option>
                            <option value="201-500" <?php if ($account['acreage_size'] == '201-500') echo 'selected'; ?>>201-500</option>
                            <option value="Above 500" <?php if ($account['acreage_size'] == 'Above 500') echo 'selected'; ?>>Above 500</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label>Crop Category:</label></th>
                    <td>
                        <select name="crop_category" id="crop_category">
                            <option value="Row Crops" <?php if ($account['crop_category'] == 'Row Crops') echo 'selected'; ?>>Row Crops</option>
                            <option value="Orchard" <?php if ($account['crop_category'] == 'Orchard') echo 'selected'; ?>>Orchard</option>
                            <option value="Vineyard" <?php if ($account['crop_category'] == 'Vineyard') echo 'selected'; ?>>Vineyard</option>
                            <option value="Pasture" <?php if ($account['crop_category'] == 'Pasture') echo 'selected'; ?>>Pasture</option>
                            <option value="Grass field" <?php if ($account['crop_category'] == 'Grass field') echo 'selected'; ?>>Grass field</option>
                            <option value="Mix" <?php if ($account['crop_category'] == 'Mix') echo 'selected'; ?>>Mix</option>
                        </select>
                    </td>
                </tr>
                <tr id="crop_mix_notes_row" style="display:none;">
                    <th><label>Crop Mix:</label></th>
                    <td><input type="text" name="crop_mix_notes" id="crop_mix_notes" value="<?php echo htmlspecialchars($account['crop_mix_notes']); ?>"></td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
						<button type="submit">Save Changes</button> 
						<a href="?view=account_view&id=<?php echo $account_id; ?>">Cancel</a>
					</td>
                </tr>
            </table>
        </form><br>
</section>


<script>
    function toggleCropMixRow() {
        const cropCategory = $('#crop_category').val();
        if (cropCategory === 'Mix') {
            $('#crop_mix_notes_row').show();
        } else {
            $('#crop_mix_notes_row').hide();
            $('#crop_mix_notes').val('');
        }
    }

    // Initialize the visibility of crop_mix_notes_row based on the current value
    $(document).ready(function() {
        toggleCropMixRow(); // Check visibility on page load
        $('#crop_category').on('change', toggleCropMixRow); // Bind change event
    });
</script>

<?php
$conn->close(); // Close the database connection
?>

