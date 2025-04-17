<?php

// Get account ID, parcel ID, and block ID from the URL
$account_id = isset($_GET['account_id']) ? intval($_GET['account_id']) : null;
$parcel_id = isset($_GET['parcel_id']) ? intval($_GET['parcel_id']) : null;
$block_id = isset($_GET['block_id']) ? intval($_GET['block_id']) : null;

// Fetch account details if account_id is provided
$business_name = '';
if ($account_id) {
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
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the data from the form
    $account_id = $_POST['account_id'] ?? null;
    $parcel_id = $_POST['parcel_id'] ?? null;
    $block_id = $_POST['block_id'] ?? null;
    $contact_id = $_POST['contact_id'] ?? null;

    // Check if account_id is missing
    if (!$account_id) {
        die("Error: Account ID is missing or invalid.");
    }

    $service_type = $_POST['service_type'] ?? 'one_time';
    $frequency = ($service_type === 'recurring') ? $_POST['frequency'] : null;
    $recurrence_end_date = ($service_type === 'recurring') ? $_POST['recurrence_end_date'] : null;

    // Call function to add service request
    add_service_request($account_id, $parcel_id, $block_id, $_POST, $service_type, $frequency, $recurrence_end_date);
}


?>

<section class="adminSection">
    <h3>Add New Service Request</h3>
	<br />
    <form method="POST">
		<div class="formInlineFieldsDisplay">
			
			<div class="formSection">
				
			<?php if ($account_id && $parcel_id && $block_id): ?>
				<label><strong>Account:</strong></label> <?php echo htmlspecialchars($business_name); ?>			
				<label><strong>Parcel ID:</strong></label> <?php echo htmlspecialchars($parcel_id); ?>			
				<label><strong>Block ID:</strong></label> <?php echo htmlspecialchars($block_id); ?>
			<?php else: ?>

				<select name="account_id" id="account_id" required>
					<option value="">Select Account</option>
					<?php
					$sql_accounts = "SELECT account_id, business_name FROM Accounts";
					$result_accounts = $conn->query($sql_accounts);
					while ($row = $result_accounts->fetch_assoc()) {
						echo "<option value='" . $row['account_id'] . "'>" . htmlspecialchars($row['business_name']) . "</option>";
					}
					?>
				</select>

				<select name="parcel_id" id="parcel_id" required disabled>
					<option value="">Select Parcel</option>
				</select>

				<select name="block_id" id="block_id" required disabled>
					<option value="">Select Block</option>
				</select>

				<select name="contact_id" id="contact_id" required disabled>
					<option value="">Select Contact</option>
				</select>
				
			</div><!--//.formSection-->
			
			<div class="formSection">
			
				<select name="type_of_service" required>
					<option value="">Select Service Type</option>
					<option value="Spray">Spray</option>
					<option value="Spread">Spread</option>
					<option value="Analyze">Analyze</option>
					<option value="Drying">Drying</option>
				</select>

				<select name="type_of_product" id="type_of_product" required>
					<option value="">Select Product Type</option>
					<option value="Pesticide">Pesticide</option>
					<option value="Herbicide">Herbicide</option>
					<option value="Fungicide">Fungicide</option>
					<option value="Chemical Thinner">Chemical Thinner</option>
					<option value="Nutrient">Nutrient</option>
					<option value="Seed">Seed</option>
					<option value="Fertilizer">Fertilizer</option>
					<option value="Rodent Control">Rodent Control</option>
					<option value="Other">Other</option>
				</select>				
				
				<input type="text" name="type_of_product_other" id="type_of_product_other" placeholder="Other Product Type:" style="display: none;">

				<input type="text" name="reason_for_application" required placeholder="Reason for Application:">

				<br />
				<input type="text" name="product_name" placeholder="Product Name:">
				<input type="text" name="supplier_name" placeholder="Supplier Name:">
				<input type="text" name="supplier_contact_phone" placeholder="Supplier Contact Phone:">
				<input type="text" name="supplier_contact_name" placeholder="Supplier Contact Name (if any):">
			
			</div><!--//.formSection-->
			
			<div class="formSection">
			
				<input type="text" name="application_need_by_date" id="application_need_by_date" readonly required placeholder="Application Need By Date">

				
				<select id="service_type" name="service_type">
					<option value="">Service Type:</option>
					<option value="one_time">One-time Service</option>
					<option value="recurring">Recurring Service</option>
				</select>

        		<span id="recurrence_options" style="display: none;">
					<select name="frequency" id="frequency">
						<option value="">Select Frequency:</option>
						<option value="weekly">Weekly</option>
						<option value="bimonthly">Bimonthly</option>
						<option value="monthly">Monthly</option>
						<option value="quarterly">Quarterly</option>
						<option value="annual">Annual</option>
					</select>
					<input type="text" name="recurrence_end_date" id="recurrence_end_date" readonly placeholder="Recurrence End Date">	
				</span><!--//end recurrence_options-->
				
				
			</div><!--//.formSection-->
			
			<div class="formSection">			
                <textarea name="comments" placeholder="Comments"></textarea>
				<br />
				<button type="submit">Add Service Request</button>
				<a href="?view=account_view&id=<?php echo $account_id; ?>">Cancel</a>				
			</div><!--//.formSection-->
				
			
            <?php endif; ?>
			
		</div><!--//.formInlineFieldsDisplay-->
        
		
    </form>
</section>

<script>

$(document).ready(function () {
	// Disable dependent dropdowns until a value is selected
	$('#account_id').on('change', function () {
		let accountId = $(this).val();
		if (accountId) {
			$('#parcel_id').prop('disabled', false);
			$('#contact_id').prop('disabled', false);

			// Fetch parcels based on accountId
			fetch('_get_parcels.php?account_id=' + accountId)
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					return response.json();
				})
				.then(data => {
					var parcelSelect = $('#parcel_id');
					parcelSelect.html('<option value="">Select Parcel</option>');
					data.forEach(function (parcel) {
						parcelSelect.append('<option value="' + parcel.parcel_id + '">' + parcel.nickname + '</option>');
					});
					$('#block_id').html('<option value="">Select Block</option>');
				})
				.catch(error => {
					console.error('There was a problem with the fetch operation:', error);
				});

			// Fetch contacts based on accountId
			fetch('_get_contacts.php?account_id=' + accountId)
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					return response.json();
				})
				.then(data => {
					var contactSelect = $('#contact_id');
					contactSelect.html('<option value="">Select Contact</option>');
					data.forEach(function (contact) {
						contactSelect.append('<option value="' + contact.user_id + '">' + contact.contact_first_name + ' ' + contact.contact_last_name + '</option>');
					});
				})
				.catch(error => {
					console.error('There was a problem with the fetch operation:', error);
				});
		} else {
			$('#parcel_id, #block_id, #contact_id').prop('disabled', true).val('');
		}
	});

	$('#parcel_id').on('change', function () {
		let parcelId = $(this).val();
		if (parcelId) {
			$('#block_id').prop('disabled', false);
			// Fetch blocks based on parcelId
			fetch('_get_blocks.php?parcel_id=' + parcelId)
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					return response.json();
				})
				.then(data => {
					var blockSelect = $('#block_id');
					blockSelect.html('<option value="">Select Block</option>');
					data.forEach(function (block) {
						blockSelect.append('<option value="' + block.block_id + '">' + block.nickname + '</option>');
					});
				})
				.catch(error => {
					console.error('There was a problem with the fetch operation:', error);
				});
		} else {
			$('#block_id, #contact_id').prop('disabled', true).val('');
		}
	});

	$('#block_id').on('change', function () {
		let blockId = $(this).val();
		if (blockId) {
			$('#contact_id').prop('disabled', false);
		} else {
			$('#contact_id').prop('disabled', true).val('');
		}
	});

	// Update recurrence options visibility based on service type
	$('#service_type').on('change', function () {
		if ($(this).val() === 'recurring') {
			$('#recurrence_options').show();
		} else {
			$('#recurrence_options').hide();
			$('#recurrence_end_date, #frequency').val('');
		}
	});
	
	$('#type_of_product').on('change', function () {
		if ($(this).val() === 'Other') {
			$('#type_of_product_other').show();
		} else {
			$('#type_of_product_other').hide();
			$('#type_of_product_other').val('');
		}
	});
});


//ALTER APPLICATION NEED BY DATE OPTIONS:
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flatpickr for Application Need By Date
    var applicationDateInput = document.getElementById('application_need_by_date');
    if (applicationDateInput) {
        flatpickr(applicationDateInput, {
            minDate: new Date().fp_incr(7), // Set minimum date to one week from today
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                var selectedDate = selectedDates[0];
                var today = new Date();
                today.setHours(0, 0, 0, 0); // Set time to midnight for comparison

                if (selectedDate < today) {
                    alert('Please select a date that is at least one week from today.');
                    instance.clear(); // Clear the invalid selection
                }
            }
        });
    }

    // Initialize Flatpickr for Recurrence End Date
    var recurrenceEndDateInput = document.getElementById('recurrence_end_date');
    if (recurrenceEndDateInput) {
        flatpickr(recurrenceEndDateInput, {
            minDate: new Date().fp_incr(7), // Set minimum date to one week from today
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                var selectedDate = selectedDates[0];
                var today = new Date();
                today.setDate(today.getDate() + 7); // One week from today
                today.setHours(0, 0, 0, 0); // Set time to midnight for comparison

                if (selectedDate < today) {
                    alert('Please select a recurrence end date that is at least one week from today.');
                    instance.clear(); // Clear the invalid selection
                }
            }
        });
    }
});

</script>

<?php
if (isset($stmt_account)) {
    $stmt_account->close();
}
$conn->close(); // Close the database connection
?>
