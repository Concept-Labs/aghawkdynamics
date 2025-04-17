<?php

//test123
// $2y$10$bwTeCKuQlD4r9ZjdrAdZ8O.3BXSY0vKOgF8zDxxfJjnuDnu3Z/9.2

// Get user ID from the URL
if (!isset($_GET['user_id']) || !isset($_GET['account_id'])) {
    die('User ID and Account ID are required.');
}
$user_id = intval($_GET['user_id']);
$account_id = intval($_GET['account_id']);

// Fetch user details from the database
$sql_user = "SELECT * FROM Accounts_Users WHERE user_id = ? AND account_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("ii", $user_id, $account_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows == 0) {
    die('User not found.');
}
$user = $result_user->fetch_assoc();

// Handle form submission by calling a function in _inc.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    update_user_details($user_id, $_POST); 
}
?>


<section class="adminSection" id="account-details">
        <h3>Edit User: <span><?php echo htmlspecialchars($user['contact_first_name'] .' '.$user['contact_last_name']); ?></span></h3>
        <form method="POST">
			<table>
				<tr>
					<td><label><strong>First Name:</strong></label></td>
					<td><input type="text" name="first_name" value="<?php echo htmlspecialchars($user['contact_first_name']); ?>" required></td>
				</tr>
				<tr>
					<td><label><strong>Last Name:</strong></label></td>
					<td><input type="text" name="last_name" value="<?php echo htmlspecialchars($user['contact_last_name']); ?>" required></td>
				</tr>
				<tr>
					<td><label><strong>Email:</strong></label></td>
					<td><input type="email" name="contact_email" value="<?php echo htmlspecialchars($user['contact_email']); ?>" required></td>
				</tr>
				<tr>
					<td><label><strong>Phone:</strong></label></td>
					<td><input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required></td>
				</tr>
				<tr>
					<td><label><strong>Role:</strong></label></td>
					<td>
						<select name="role" required>
							<option value="">Select Role</option>
							<option value="Account Admin" <?php if ($user['role'] == 'Account Admin') echo 'selected'; ?>>Account Admin</option>
							<option value="Account Contact" <?php if ($user['role'] == 'Account Contact') echo 'selected'; ?>>Account Contact</option>
							<option value="Billing Contact" <?php if ($user['role'] == 'Billing Contact') echo 'selected'; ?>>Billing Contact</option>
						</select>
					</td>
				</tr>
				<tr>
					<td><label><strong>Password:</strong></label></td>
					<td><input type="password" name="password"> (leave blank to remain unchanged)</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<button type="submit">Update User</button>
						<a href="?view=account_view&id=<?php echo $account_id; ?>">Cancel</a>
					</td>
				</tr>
			</table>
		</form>
</section>

<?php
$conn->close(); // Close the database connection
?>
