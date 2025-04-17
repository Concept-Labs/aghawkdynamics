<?php

// Get account ID from the URL
if (!isset($_SESSION['account_id'])) {
    die('Account ID is required.');
}
$account_id = intval($_SESSION['account_id']);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action']=='updateProfile') {
    update_profile($account_id, $_POST);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action']=='updateUser') {
    $account_user_id = intval($_POST['account_user_id']);
    update_user($account_user_id, $account_id, $_POST);
}

// Handle Deactivate User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'deactivateUser' && isset($_POST['account_user_id'])) {
    $account_user_id = intval($_POST['account_user_id']);
    deactivate_user($account_user_id, $_POST);
}

// Handle Activate User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'activateUser' && isset($_POST['account_user_id'])) {
    $account_user_id = intval($_POST['account_user_id']);
    activate_user($account_user_id, $_POST);
}


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

// Fetch users associated with the account
$sql_account_users = "SELECT * FROM Accounts_Users 
              WHERE account_id = ? 
              ORDER BY 
                  CASE 
                      WHEN role = 'Account Admin' THEN 1
                      WHEN role = 'Billing Contact' THEN 2
                      WHEN role = 'Account Contact' THEN 3
                      ELSE 4
                  END, role ASC";

$stmt_account_users = $conn->prepare($sql_account_users);
$stmt_account_users->bind_param("i", $account_id);
$stmt_account_users->execute();
$result_account_users = $stmt_account_users->get_result();

$account_users = []; // Store users in an array
$billingEmail = null; // Default value

// Fetch all users and capture Billing Contact separately
while ($account_user = $result_account_users->fetch_assoc()) {
    $account_users[] = $account_user; // Store all users in the array

    // Assign Billing Contact email if found
    if ($account_user['role'] === 'Billing Contact' && !$billingEmail) {
        $billingEmail = $account_user['contact_email'];
    }
}


?>


<?php 
    if(isset($_SESSION['displayMsg'])) { 
?>
        <div class="displayMsg"><?= $_SESSION['displayMsg']; ?></div>
        <script>
            $(document).ready(function() {								
                if ($('.displayMsg').length) {// Check if the displayMsg element exists									
                    $('.displayMsg').delay(6000).slideUp(500, function() {
                        $(this).remove();
                    });
                }
            });
        </script>
<?php
        unset($_SESSION['displayMsg']);
    } //end displayMsg
?>


<h3>My Profile</h3>

<p><hr /></p>

<div id="deactivateModal" class="modal">
    <div class="modal-content">
        <h4>Confirm Deactivation</h4>
        <br />
        <p>Are you sure you want to deactivate <strong><span id="userName"></span></strong>?</p>
        <form action="profile" method="post">
            <input type="hidden" name="action" value="deactivateUser" />
            <input type="hidden" name="account_user_id" value="" />
            <textarea name="deactivateNotes" placeholder="Reason for deactivation?"></textarea>
            <div class="modal-buttons">
                <button id="confirmDeactivate" class="confirm-btn">Deactivate</button>
                <button id="cancelDeactivate" class="cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<section class="userSection Profile">

    <div class="pageHdrActions">
        <a class="addEditLink" href="profile_edit" title="Edit Profile (Coming Soon)"><i class="fa-solid fa-pen-to-square"></i> Edit Profile</a>
    </div>
    
    <table class="blockTable">
    <thead>
        <tr>
            <th colspan="2">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Business Name:</td>
            <td><? echo htmlspecialchars_decode($account['business_name']); ?></td>
        </tr>
        <tr>
            <td>Business Phone:</td>
            <td><? echo htmlspecialchars_decode($account['business_phone']); ?></td>
        </tr>
        <tr>
            <td>Mailing Address:</td>
            <td><? echo htmlspecialchars_decode($account['street_address']); ?><br />
                <? echo htmlspecialchars_decode($account['city'].' '.$account['state'].' '.$account['zip']); ?>
            </td>
        </tr>
        <tr>
            <td>Billing Address:</td>
            <td><? echo htmlspecialchars_decode($account['billing_address']); ?><br />
                <? echo htmlspecialchars_decode($account['billing_city'].' '.$account['billing_state'].' '.$account['billing_zip']); ?>
            </td>
        </tr>
        <tr>
            <td>Billing Email:</td>
            <td><? echo $billingEmail; ?></td>
        </tr>
        <tr>
            <td>Crop Size:</td>
            <td><? echo htmlspecialchars_decode($account['acreage_size']); ?> acres</td>
        </tr>
        </tbody>
    </table>
</section>

<p>&nbsp;</p>

<h3>Account Contacts</h3>

<p><hr /></p>

    <div class="pageHdrActions">
        <a class="addEditLink" href="user_add" title="Add User (Coming Soon)"><i class="fa-solid fa-user-plus"></i> Add Contact</a>
    </div>

<table class="blockTable">
    <thead>
        <tr>
            <th>Contact Name</th>
            <th>Email Address</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($account_users as $account_user): ?>
            <?php $wholeName = htmlspecialchars($account_user['contact_first_name'] . ' ' . $account_user['contact_last_name']); ?>
            <tr>
                <td><?= $wholeName; ?></td>
                <td><?= htmlspecialchars($account_user['contact_email']); ?></td>
                <td><?= htmlspecialchars($account_user['phone']); ?></td>
                <td><?= htmlspecialchars($account_user['role']); ?></td>
                
                <td class="actions">
                    <? if($_SESSION['user_role'] == 'Account Admin') { ?>
                    
                        <a class="addEditLink" href="user_edit?account_user_id=<?= $account_user['account_user_id']; ?>" title="Edit User (coming soon)">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    
                        <?php
                            $isActive = ($account_user['status'] == 'active');
                            $toggleStatus = $isActive ? "on" : "off";
                            $titleText = $isActive ? "Deactivate" : "Activate";
                        ?>
                        <a href="javascript:void(0);" class="iconToggle deactivateUser" data-user-action="<?= $titleText; ?>" data-user-id="<?= $account_user['account_user_id']; ?>" data-user-name="<?= $wholeName; ?>" title="<?= $titleText; ?> User">
                            <i class="fa-solid fa-toggle-<?= $toggleStatus; ?>"></i> 
                        </a>
                    
                    <? } else if($_SESSION['account_user_id'] == $account_user['account_user_id']) { ?>
                    
                        <a class="addEditLink" href="user_edit?account_user_id=<?= $account_user['account_user_id']; ?>" title="Edit User">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    
                    <? } ?>
                </td>
                
            </tr>
        <?php endforeach; ?>
        
    </tbody>
</table>

<p>&nbsp;</p>

<script>
$(document).ready(function () {
    $(".deactivateUser").on("click", function () {
        let accountUserId = $(this).data("user-id"); // Get User ID
        let userName = $(this).data("user-name"); // Get User Name
        let action = $(this).data("user-action"); // Get Action (Activate/Deactivate)

        // Update modal text and input
        $("#userName").text(userName);
        $("input[name='account_user_id']").val(accountUserId);
        $("input[name='action']").val(action == "Deactivate" ? "deactivateUser" : "activateUser");

        // Change button text based on action
        $("#confirmDeactivate").text(action);

        // Show modal
        $("#deactivateModal").fadeIn().css("display", "flex");
    });

    // Confirm deactivate/activate (Submit Form)
    $("#confirmDeactivate").on("click", function (e) {
        e.preventDefault();
        $("#deactivateModal form").submit(); 
    });

    // Cancel action (Close Modal)
    $("#cancelDeactivate").on("click", function (e) {
        e.preventDefault();
        $("#deactivateModal").fadeOut();
    });

    // Close modal if user clicks outside of it
    $("#deactivateModal").on("click", function (e) {
        if ($(e.target).is("#deactivateModal")) {
            $(this).fadeOut();
        }
    });
});


</script>
