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
    add_account_user($account_id, $_POST);
}

?>

<section class="adminSection">
    <h3>Add New User:</h3>
    <form method="POST">
        <table>
            <tr>
                <td><label><strong>Customer Account:</strong></label></td>
                <td><h4 class="green"><?php echo htmlspecialchars($business_name); ?></h4></td>
            </tr>
            <tr>
                <td><label><strong>First Name:</strong></label></td>
                <td><input type="text" name="first_name" required></td>
            </tr>
            <tr>
                <td><label><strong>Last Name:</strong></label></td>
                <td><input type="text" name="last_name" required></td>
            </tr>
            <tr>
                <td><label><strong>Email:</strong></label></td>
                <td><input type="email" name="contact_email" required></td>
            </tr>
            <tr>
                <td><label><strong>Phone:</strong></label></td>
                <td><input type="text" name="phone" required></td>
            </tr>
            <tr>
                <td><label><strong>Password:</strong></label></td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td><label><strong>Role:</strong></label></td>
                <td>
                    <select name="role" required>
                        <option value="">Select Role</option>
							<option value="Account Admin">Account Admin</option>
							<option value="Account Contact">Account Contact</option>
							<option value="Billing Contact">Billing Contact</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <button type="submit">Add User</button>
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
