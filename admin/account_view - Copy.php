<?php

// Get account ID from the URL
if (!isset($_GET['id'])) {
    die('Account ID is required.');
}
$account_id = intval($_GET['id']);

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

// Fetch users associated with this account
$sql_users = "SELECT * FROM Accounts_Users WHERE account_id = ?";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->bind_param("i", $account_id);
$stmt_users->execute();
$result_users = $stmt_users->get_result();
$users = $result_users->fetch_all(MYSQLI_ASSOC);

// Fetch parcels associated with this account
$sql_parcels = "SELECT * FROM Parcels WHERE account_id = ?";
$stmt_parcels = $conn->prepare($sql_parcels);
$stmt_parcels->bind_param("i", $account_id);
$stmt_parcels->execute();
$result_parcels = $stmt_parcels->get_result();
$parcels = $result_parcels->fetch_all(MYSQLI_ASSOC);
?>


        <section class="adminSection" id="account-details">
            <h3>Account Details: <span><?php echo htmlspecialchars($account['business_name']); ?></span> <a class="addEditLink" href="?view=account_edit&id=<? echo $_GET['id']; ?>">Edit Account</a></h3>
            <table border="1">
                <tr><th>Business Name</th><td><?php echo htmlspecialchars($account['business_name']); ?></td></tr>
                <tr><th>Business Phone</th><td><?php echo htmlspecialchars($account['business_phone']); ?></td></tr>
                <tr><th>Street Address</th><td><?php echo htmlspecialchars($account['street_address']); ?></td></tr>
                <tr><th>City</th><td><?php echo htmlspecialchars($account['city']); ?></td></tr>
                <tr><th>State</th><td><?php echo htmlspecialchars($account['state']); ?></td></tr>
                <tr><th>Zip Code</th><td><?php echo htmlspecialchars($account['zip']); ?></td></tr>
                <tr><th>Acreage Size</th><td><?php echo htmlspecialchars($account['acreage_size']); ?></td></tr>
                <tr><th>Crop Category</th><td><?php echo htmlspecialchars($account['crop_category']); ?></td></tr>
            </table>
        </section>

        <section class="adminSection" id="contacts">
            <h3>Contacts: <span><?php echo htmlspecialchars($account['business_name']); ?></span> <a class="addEditLink" href="?view=account_user_add&id=<? echo $_GET['id']; ?>">Add New Contact</a></h3>
            <table border="1">
                <tr><th>Contact Name</th><th>Email</th><th>Phone</th></tr>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><a href="?view=account_user_edit&user_id=<? echo $user['user_id']; ?>&account_id=<? echo $account_id; ?>" title="Click to Edit"><?php echo htmlspecialchars($user['contact_first_name'] . ' ' . $user['contact_last_name']); ?></a></td>
                        <td><?php echo htmlspecialchars($user['contact_email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <section class="adminSection" id="parcels">
            <h3>Parcels:<span><?php echo htmlspecialchars($account['business_name']); ?></span> <a class="addEditLink" href="?view=parcel_add&id=<? echo $_GET['id']; ?>">Add New Parcel</a></h3>
            <table border="1">
                <tr>
                    <th>Parcel Number</th>
                    <th>Nickname</th>
                    <th>Street Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Zip Code</th>
                    <th>Acres</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Usage Type</th>
                    <th>Created At</th>
                </tr>
                <?php foreach ($parcels as $parcel): ?>
                    <tr>
                        <td><a href="?view=parcel_edit&parcel_id=<? echo $parcel['parcel_id']; ?>&account_id=<? echo $account_id; ?>" title="Click to Edit"><?php echo htmlspecialchars($parcel['parcel_number']); ?></a></td>
                        <td><?php echo htmlspecialchars($parcel['nickname']); ?></td>
                        <td><?php echo htmlspecialchars($parcel['street_address']); ?></td>
                        <td><?php echo htmlspecialchars($parcel['city']); ?></td>
                        <td><?php echo htmlspecialchars($parcel['state']); ?></td>
                        <td><?php echo htmlspecialchars($parcel['zip']); ?></td>
                        <td><?php echo htmlspecialchars($parcel['acres']); ?></td>
                        <td><?php echo htmlspecialchars($parcel['latitude']); ?></td>
                        <td><?php echo htmlspecialchars($parcel['longitude']); ?></td>
                        <td><?php echo htmlspecialchars($parcel['crop_category']); ?></td>
                        <td><?php echo htmlspecialchars($parcel['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>

<?php
$conn->close(); // Close the database connection
?>
