<?php

$account_id = intval($_SESSION['account_id']);

// Fetch account details from the database
$sql_account = "SELECT * FROM Accounts WHERE account_id = ?";
$stmt_account = $conn->prepare($sql_account);
$stmt_account->bind_param("i", $account_id);
$stmt_account->execute();
$result_account = $stmt_account->get_result();

if ($result_account->num_rows == 0) { //destroy session and log out
	session_unset();
	session_destroy();
	session_start();
    $_SESSION['displayMsg'] = 'Account not found.';
	header("Location:https://my.aghawkdynamics.com/");
}
$account = $result_account->fetch_assoc();


// Fetch parcels associated with the account
$sql_parcels = "SELECT * FROM Parcels WHERE account_id = ?";
$stmt_parcels = $conn->prepare($sql_parcels);
$stmt_parcels->bind_param("i", $account_id);
$stmt_parcels->execute();
$result_parcels = $stmt_parcels->get_result();
?>



<h4><?php echo htmlspecialchars_decode($account['business_name']); ?> Dashboard</h4>










