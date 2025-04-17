<?php

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    add_account($_POST);
}


?>

<section class="adminSection">
    <h3>Add New Account</h3>
    <form method="POST">
        <table>
            <tr>
                <td><label><strong>Business Name:</strong></label></td>
                <td><input type="text" name="business_name" required></td>
            </tr>
            <tr>
                <td><label><strong>Street Address:</strong></label></td>
                <td><input type="text" name="street_address" required></td>
            </tr>
            <tr>
                <td><label><strong>City:</strong></label></td>
                <td><input type="text" name="city" required></td>
            </tr>
            <tr>
                <td><label><strong>State:</strong></label></td>
                <td><input type="text" name="state" required></td>
            </tr>
            <tr>
                <td><label><strong>Zip Code:</strong></label></td>
                <td><input type="text" name="zip" required></td>
            </tr>
            <tr>
                <td><label><strong>Business Phone:</strong></label></td>
                <td><input type="text" name="business_phone" required></td>
            </tr>
            <tr>
                <td><label><strong>Acreage Size:</strong></label></td>
                <td>
                    <select name="acreage_size" required>
                        <option value="">Select Acreage Size</option>
                        <option value="Under 50">Under 50</option>
                        <option value="50-200">50-200</option>
                        <option value="201-500">201-500</option>
                        <option value="Above 500">Above 500</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label><strong>Crop Category:</strong></label></td>
                <td>
                    <select name="crop_category" id="crop_category" required>
                        <option value="">Select Crop Category</option>
                        <option value="Row Crops">Row Crops</option>
                        <option value="Orchard">Orchard</option>
                        <option value="Vineyard">Vineyard</option>
                        <option value="Pasture">Pasture</option>
                        <option value="Grass field">Grass field</option>
                        <option value="Mix">Mix</option>
                    </select>
                </td>
            </tr>
			<tr id="crop_mix_notes_row" style="display:none;">
				<th><label>Crop Mix:</label></th>
				<td><input type="text" name="crop_mix_notes" id="crop_mix_notes" value="<?php echo htmlspecialchars($account['crop_mix_notes']); ?>"></td>
			</tr>
            <tr>
                <td colspan="2" align="center">
                    <button type="submit">Add Account</button>
                    <a href="?view=accounts">Cancel</a>
                </td>
            </tr>
        </table>
    </form>
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
