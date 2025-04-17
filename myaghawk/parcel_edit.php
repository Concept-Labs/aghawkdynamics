<?php

// Get parcel ID from the URL
if (!isset($_GET['parcel_id']) || !isset($_SESSION['account_id'])) {
    die('Parcel ID and Account ID are required.');
}
$parcel_id = intval($_GET['parcel_id']);
$account_id = intval($_SESSION['account_id']);


// Handle form submission by calling a function in _inc.php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action']=='editParcel') {
    update_parcel_details($parcel_id, $account_id, $_POST);
}


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

?>


<?php 
    if(isset($_SESSION['displayMsg'])) { 
?>
        <div class="displayMsg"><?php echo $_SESSION['displayMsg']; ?></div>
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

<section class="adminSection">
    <h3>Edit Parcel: <span><?php echo htmlspecialchars($parcel['nickname']); ?></span></h3>
    
    <p>&nbsp;</p>
    
    <form method="POST" action="parcel_detail?parcel_id=<?= $parcel['parcel_id']; ?>">
        <input type="hidden" name="action" value="editParcel" />
        <input type="hidden" name="parcel_id" value="<?= $parcel['parcel_id']; ?>" />
        <div class="formInlineFieldsWrap">
            <label>Nickname:</label>
            <input type="text" name="nickname" value="<?php echo htmlspecialchars($parcel['nickname']); ?>" required>
        </div>
        <div class="formInlineFieldsWrap">
            <label>Street Address:</label>
            <input type="text" name="street_address" value="<?php echo htmlspecialchars($parcel['street_address']); ?>" required>
        </div>
        <div class="formInlineFieldsWrap">
            <label>City:</label>
            <input type="text" name="city" value="<?php echo htmlspecialchars($parcel['city']); ?>" required>
        </div>
        <div class="formInlineFieldsWrap">
            <label>State:</label>
            <input type="text" name="state" value="<?php echo htmlspecialchars($parcel['state']); ?>" required>
        </div>
        <div class="formInlineFieldsWrap">
            <label>Zip Code:</label>
            <input type="text" name="zip" value="<?php echo htmlspecialchars($parcel['zip']); ?>" required>
        </div>
        <div class="formInlineFieldsWrap">
            <label>Acres:</label>
            <input type="text" name="acres" value="<?php echo htmlspecialchars($parcel['acres']); ?>" required>
        </div>     
        <p>
            <button type="submit">Save Changes</button> &nbsp;  
            <a href="parcels">Cancel &amp; return to Parcels &raquo;</a>
        </p>
    </form>
</section>

<?php
$conn->close(); // Close the database connection
?>