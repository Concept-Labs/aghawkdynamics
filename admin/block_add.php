<?php

// Get parcel ID and account ID from the URL
if (!isset($_GET['parcel_id']) || !isset($_GET['account_id'])) {
    die('Parcel ID and Account ID are required.');
}
$parcel_id = intval($_GET['parcel_id']);
$account_id = intval($_GET['account_id']);

// Fetch parcel details
$sql_parcel = "SELECT * FROM Parcels WHERE parcel_id = ?";
$stmt_parcel = $conn->prepare($sql_parcel);
$stmt_parcel->bind_param("i", $parcel_id);
$stmt_parcel->execute();
$result_parcel = $stmt_parcel->get_result();

if ($result_parcel->num_rows == 0) {
    die('Parcel not found.');
}
$parcel = $result_parcel->fetch_assoc();
$parcelNickname = $parcel['nickname'];

// Fetch account details
$sql_account = "SELECT * FROM Accounts WHERE account_id = ?";
$stmt_account = $conn->prepare($sql_account);
$stmt_account->bind_param("i", $account_id);
$stmt_account->execute();
$result_account = $stmt_account->get_result();

if ($result_account->num_rows == 0) {
    die('Account not found.');
}
$account = $result_account->fetch_assoc();
$business_name = $account['business_name'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    add_new_block($parcel_id, $account_id, $_POST);
}

?>

<section class="adminSection">
    <h3>Add New Block</h3>
    <form method="POST">
        <table>
            <tr>
                <td><label><strong>Customer Account:</strong></label></td>
                <td><?php echo htmlspecialchars($business_name); ?></td>
            </tr>
            <tr>
                <td><label><strong>Parcel Nickname:</strong></label></td>
                <td><?php echo htmlspecialchars($parcelNickname); ?></td>
            </tr>
            <tr>
                <td><label><strong>Block Nickname:</strong></label></td>
                <td><input type="text" name="nickname" required></td>
            </tr>
            <tr>
                <td><label><strong>Acres:</strong></label></td>
                <td><input type="number" name="acres" step="0.01" required></td>
            </tr>
            <tr>
                <td><label><strong>Latitude:</strong></label></td>
                <td><input type="text" name="latitude" required></td>
            </tr>
            <tr>
                <td><label><strong>Longitude:</strong></label></td>
                <td><input type="text" name="longitude" required></td>
            </tr>
            <tr>
                <td><label><strong>Usage Type:</strong></label></td>
                <td>
                    <select name="crop_category" required>
                        <option value="">Select Usage Type</option>
                        <option value="Orchard">Orchard</option>
                        <option value="Vineyard">Vineyard</option>
                        <option value="Row Crops">Row Crops</option>
                        <option value="Pasture">Pasture</option>
                        <option value="Grass field">Grass field</option>
                        <option value="Mix">Mix</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label><strong>Notes:</strong></label></td>
                <td><textarea name="notes" rows="4"></textarea></td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <button type="submit">Add Block</button>
                    <a href="?view=account_view&id=<?php echo $account_id; ?>">Cancel</a>
                </td>
            </tr>
        </table>
    </form>
</section>

<?php
$stmt_parcel->close();
$stmt_account->close();
$conn->close(); // Close the database connection
?>
