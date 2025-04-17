<?php

// Get account ID from the URL
if (!isset($_GET['account_id'])) {
    die('Account ID is required.');
}
$account_id = intval($_GET['account_id']);

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
    add_parcel($account_id, $_POST);
}

?>

<section class="adminSection">
    <h3>Add New Parcel:</h3>
    <form method="POST">
        <table>
            <tr>
                <td><label><strong>Customer Account:</strong></label></td>
                <td><h4 class="green"><?php echo htmlspecialchars($business_name); ?></h4></td>
            </tr>
            <tr>
                <td><label><strong>Parcel Nickname:</strong></label></td>
                <td><input type="text" name="nickname" required></td>
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
                <td colspan="2" align="center">
                    <button type="submit">Add Parcel</button>
                    <a href="?view=account_view&id=<?php echo $account_id; ?>">Cancel</a>
                </td>
            </tr>
        </table>
    </form>
</section>

<?php
$stmt_account->close();
$conn->close(); // Close the database connection
?>
