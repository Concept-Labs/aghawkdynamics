<?php

// Get account ID from the URL
if (!isset($_SESSION['account_id'])) {
    die('Account ID is required.');
}
$account_user_id = intval($_SESSION['account_user_id']);

// Fetch account details from the database
$sql_account = "SELECT * FROM Accounts_Users WHERE account_user_id = ?";
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

?>


<h3>Edit Profile</h3>

<p><hr /></p>


<section class="userSection Profile">

    <form action="profile" method="POST">
        <input type="hidden" name="action" value="updateUser" />
        <input type="hidden" name="account_user_id" value="<?= $_GET['account_user_id']; ?>" placeholder="" />
        
        <table class="blockTable">
        <thead>
            <tr>
                <th colspan="2">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Business Name:</td>
                <td><input type="text" name="business_name" id="business_name" value="<?= $account['business_name']; ?>" placeholder="Business Name:" /></td>
            </tr>
            <tr>
                <td>Business Phone:</td>
                <td><input type="text" name="business_phone" id="business_phone" value="<?= $account['business_phone']; ?>" placeholder="Business Phone:" /></td>
            </tr>
            <tr>
                <td>Mailing Address:</td>
                <td><input type="text" name="street_address" id="street_address" value="<?= $account['street_address']; ?>" placeholder="Street Address:" /><br />
                    <input type="text" name="city" id="city" value="<?= $account['city']; ?>" placeholder="City:" /><br />
                    <input type="text" name="state" id="state" value="<?= $account['state']; ?>" placeholder="State:" /><br />
                    <input type="text" name="zip" id="zip" value="<?= $account['zip']; ?>" placeholder="Zip Code:" />
                </td>
            </tr>
            <tr>
                <td>Billing Address:</td>
                <td><input type="text" name="billing_address" id="billing_address" value="<?= $account['billing_address']; ?>" placeholder="Billing Address:" /><br />
                    <input type="text" name="billing_city" id="billing_city" value="<?= $account['billing_city']; ?>" placeholder="Billing City:" /><br />
                    <input type="text" name="billing_state" id="billing_state" value="<?= $account['billing_state']; ?>" placeholder="Billing State:" /><br />
                    <input type="text" name="billing_zip" id="billing_zip" value="<?= $account['billing_zip']; ?>" placeholder="Billing Zip:" />
                </td>
            </tr>
            <tr>
                <td>Crop Size:</td>
                <td>
                    <select name="acreage_size">
                        <option value="" >Please Select:</option>
                        <option value="Under 50" <?php if ($account['acreage_size'] == 'Under 50') echo 'selected'; ?>>Under 50</option>
                        <option value="50-200" <?php if ($account['acreage_size'] == '50-200') echo 'selected'; ?>>50-200</option>
                        <option value="201-500" <?php if ($account['acreage_size'] == '201-500') echo 'selected'; ?>>201-500</option>
                        <option value="Above 500" <?php if ($account['acreage_size'] == 'Above 500') echo 'selected'; ?>>Above 500</option>
                    </select> acres</td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type="submit" id="submitBtn">Update Profile</button> &nbsp; 
                    <a href="profile">Cancel &raquo;</a>
                </td>
            </tr>
            </tbody>
        </table>
    
    </form>
    
</section>

<p>&nbsp;</p>
