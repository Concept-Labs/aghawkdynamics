<?php

// Get parcel ID from the URL
if (!isset($_GET['parcel_id']) || !isset($_GET['account_id'])) {
    die('Parcel ID and Account ID are required.');
}
$parcel_id = intval($_GET['parcel_id']);
$account_id = intval($_GET['account_id']);

// Fetch parcel details from the database
$sql_parcel = "SELECT * FROM Parcels WHERE parcel_id = ? AND account_id = ?";
$stmt_parcel = $conn->prepare($sql_parcel);
$stmt_parcel->bind_param("ii", $parcel_id, $account_id);
$stmt_parcel->execute();
$result_parcel = $stmt_parcel->get_result();

if ($result_parcel->num_rows == 0) {
    die('Parcel not found.');
}
$parcel = $result_parcel->fetch_assoc();


// Fetch account details
$sql_account = "SELECT * FROM Accounts WHERE account_id = ?";
$stmt_account = $conn->prepare($sql_account);
$stmt_account->bind_param("i", $account_id);
$stmt_account->execute();
$result_account = $stmt_account->get_result();
$account = $result_account->fetch_assoc();
$business_name = $account['business_name'];

// Handle form submission by calling a function in _inc.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    update_parcel_details($parcel_id, $account_id, $_POST);
}
?>

<section class="adminSection">
        <h3>Edit Parcel: <span><?php echo htmlspecialchars($parcel['nickname']); ?></span></h3>
		<p>Account: <strong><? echo $business_name; ?></strong></p>
        <form method="POST">
            <table border="1">
                <tr>
                    <th><label>Parcel Number:</label></th>
                    <td><input type="text" name="parcel_number" value="<?php echo htmlspecialchars($parcel['parcel_number']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>Nickname:</label></th>
                    <td><input type="text" name="nickname" value="<?php echo htmlspecialchars($parcel['nickname']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>Street Address:</label></th>
                    <td><input type="text" name="street_address" value="<?php echo htmlspecialchars($parcel['street_address']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>City:</label></th>
                    <td><input type="text" name="city" value="<?php echo htmlspecialchars($parcel['city']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>State:</label></th>
                    <td><input type="text" name="state" value="<?php echo htmlspecialchars($parcel['state']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>Zip Code:</label></th>
                    <td><input type="text" name="zip" value="<?php echo htmlspecialchars($parcel['zip']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>Acres:</label></th>
                    <td><input type="text" name="acres" value="<?php echo htmlspecialchars($parcel['acres']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>Latitude:</label></th>
                    <td><input type="text" name="latitude" value="<?php echo htmlspecialchars($parcel['latitude']); ?>" required></td>
                </tr>
                <tr>
                    <th><label>Longitude:</label></th>
                    <td><input type="text" name="longitude" value="<?php echo htmlspecialchars($parcel['longitude']); ?>" required></td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
						<button type="submit">Save Changes</button> 
						<a href="?view=account_view&id=<?php echo $account_id; ?>">Cancel</a>
					</td>
                </tr>
            </table>
        </form>
</section>

<?php
$conn->close(); // Close the database connection
?>