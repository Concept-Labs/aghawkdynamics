<?php

// Get parcel ID and account ID from the URL
if (!isset($_SESSION['account_id'])) {
    die('Account ID is required.');
}

$account_id = intval($_SESSION['account_id']);
$parcel_id = isset($_GET['parcel_id']) ? intval($_GET['parcel_id']) : null;


if ($parcel_id) {
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
}

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

<section class="userSection">
    <h3>Add New Block</h3>

    <p><hr /></p>

    <form method="POST">
        
        <?php if($parcel_id) { ?>
            <input type="hidden" name="parcel_id" value="<?php echo $parcel_id; ?>" />
            <p><strong>Parcel Nickname:</strong> <?php echo htmlspecialchars($parcelNickname); ?></p>        
        <?php } ?>
        
        <p>
            
        <?php if(!$parcel_id) { ?>            
			<select name="parcel_id" id="parcel_id" required disabled>
				<option value="">Select Parcel</option>
			</select>
        <?php } ?>
            
            <input type="text" name="nickname" required placeholder="Block Nickname:">
            <input type="number" name="acres" step="0.01" required placeholder="Acres:">
            <select name="crop_category" required>
                <option value="">Select Usage Type</option>
                <option value="Orchard">Orchard</option>
                <option value="Vineyard">Vineyard</option>
                <option value="Row Crops">Row Crops</option>
                <option value="Pasture">Pasture</option>
                <option value="Grass field">Grass field</option>
                <option value="Mix">Mix</option>
            </select>
        </p>
        
        <p>
            <textarea name="notes" rows="4" placeholder="">Notes</textarea>
        </p>
        
        <p>    
            <button type="submit">Add Block</button> &nbsp; 
            <a href="blocks?parcel_id=<?php echo $parcel_id; ?>">Cancel</a>
        </p>
                
            
        </table>
    </form>
</section>



<?php
    if($parcel_id) {
        $stmt_parcel->close();
        $stmt_account->close();
        $conn->close(); // Close the database connection
    } else {
?>

<script>
$(document).ready(function () {
	// Pre-populate the Parcels select menu
    const accountId = <?php echo json_encode($_SESSION['account_id']); ?>;
    if (accountId) {
        fetch('_get_parcels.php?account_id=' + accountId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const parcelSelect = $('#parcel_id');
                parcelSelect.html('<option value="">Select Parcel</option>');
                data.forEach(parcel => {
                    parcelSelect.append(`<option value="${parcel.parcel_id}">${parcel.nickname}</option>`);
                });
                $('#parcel_id').prop('disabled', false);
            })
            .catch(error => console.error('Error fetching parcels:', error));
    }    
});
</script>

<?php
    }
?>
