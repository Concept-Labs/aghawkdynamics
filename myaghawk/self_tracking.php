<?php
// Get account ID
$account_id = $_SESSION['account_id'];


?>
<section class="userSection">
    <h3>Self-Tracking Activity</h3>

    <p><hr /></p>
    
    <p style="background:yellow; padding:5px; width:fit-content;"> - TESTING - </p>
    <form method="POST" action="add_self_tracking_activity">
		<input type="hidden" name="account_id" value="<?php echo $_SESSION['account_id']; ?>" />
		<input type="hidden" name="action" value="add_self_tracking_activity" />	
			
		<p><strong>Select Location:</strong></p>
		<p>
			<select name="parcel_id" id="parcel_id" required disabled>
				<option value="">Select Parcel</option>
			</select>

			<select name="block_id" id="block_id" required disabled>
				<option value="">Select Block</option>
			</select>
		</p>
		
		<p><strong>Completed Application Details:</strong></p>
		<p>
			<select name="type_of_service" required>
				<option value="">Select Service Type</option>
				<option value="Spray">Spray</option>
				<option value="Spread">Spread</option>
				<option value="Analyze">Analyze</option>
				<option value="Drying">Drying</option>
			</select>

			<input type="text" name="reason_for_application" required placeholder="Reason for Application:">                        
			
            <select name="type_of_product" id="type_of_product">
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
            
            <br class="Clear" />
            
            <input type="number" name="volume" id="volume" placeholder="Volume:" maxlength="4" >
            <select name="uom" id="uom">
                <option value="">Select Volume OUM</option>
                <option value="Ounces">Ounces</option>
                <option value="Pounds">Pounds</option>
                <option value="Liters">Liters</option>
                <option value="Gallons">Gallons</option>
                <option value="Other">Other</option>
            </select>
            <input type="text" name="uom_other" id="uom_other" placeholder="Other OUM:" style="display:none;">
            
            <br class="Clear" />
            
			<input type="text" name="completed_date" id="completed_date" placeholder="Completed Date" class="flatpickr">
            <input type="text" name="completed_by" id="completed_by" placeholder="Completed By:" >
            <input type="number" name="temperature" id="temperature" placeholder="Temperature: (&deg;F)" maxlength="3" >
            <input type="number" name="wind" id="wind" placeholder="Wind: (mph)" maxlength="3" >
            
            <br class="Clear" />
            
            <input style="width:335px;" type="number" name="restricted_exposure_hrs" id="restricted_exposure_hrs" placeholder="Restricted Exposure Hours: (per label)" maxlength="3" >
		</p>
			
		<p class="ProductSupplierInfo"><strong>Product Supplier Information:</strong><br />
			<input type="text" name="supplier_name" placeholder="Supplier Name:">
			<input type="text" name="supplier_contact_phone" id="supplier_contact_phone" placeholder="Supplier Contact Phone:">				
			<input type="text" name="supplier_contact_name" placeholder="Supplier Contact Name:">						
			<input type="text" name="supplier_contact_email" placeholder="Supplier Contact Email:">			
		</p>

		<p><strong>Is this a recurring activity?</strong><br />
			<label><input type="radio" name="service_type" value="recurring" required> Yes</label> &nbsp;
			<label><input type="radio" name="service_type" value="one_time" required> No</label>
		</p>

		<p class="recurringServiceInfo" style="display:none;">
			<select name="frequency" id="frequency">
				<option value="">Select Frequency:</option>
				<option value="weekly">Weekly</option>
				<option value="bimonthly">Bimonthly</option>
				<option value="monthly">Monthly</option>
				<option value="quarterly">Quarterly</option>
				<option value="annual">Annual</option>
			</select>
			
			<input type="text" name="recurrence_end_date" id="recurrence_end_date" placeholder="Recurrence End Date" class="flatpickr">
			
			</span>
		</p>


        <div class="labelAndInputWrap">
            <textarea name="comments" placeholder="Comments:"><?php echo htmlspecialchars($service_request['comments']); ?></textarea>
        </div>

		<p><button type="submit">Save Self-Tracking Info</button></p>        
		
    </form>

</section>


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

    // Populate Blocks select menu based on selected Parcel
    $('#parcel_id').on('change', function () {
        const parcelId = $(this).val();
        if (parcelId) {
            fetch('_get_blocks.php?parcel_id=' + parcelId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const blockSelect = $('#block_id');
                    blockSelect.html('<option value="">Select Block</option>');
                    data.forEach(block => {
                        blockSelect.append(`<option value="${block.block_id}">${block.nickname}</option>`);
                    });
                    $('#block_id').prop('disabled', false);
                })
                .catch(error => console.error('Error fetching blocks:', error));
        } else {
            $('#block_id').html('<option value="">Select Block</option>').prop('disabled', true);
        }
    });
	
	if (accountId) {
		// Populate Contacts select menu based on account_id
		$('#block_id').on('change', function () {
			fetch('_get_contacts.php?account_id=' + accountId)
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					return response.json();
				})
				.then(data => {
					const contactSelect = $('#contact_id');
					contactSelect.html('<option value="">Select Contact</option>');
					data.forEach(contact => {
						contactSelect.append(
							`<option value="${contact.user_id}">${contact.contact_first_name} ${contact.contact_last_name}</option>`
						);
					});
					$('#contact_id').prop('disabled', false);
				})
				.catch(error => console.error('Error fetching contacts:', error));
		});
	} else {
		$('#contact_id').html('<option value="">Select Contact</option>').prop('disabled', true);
	}


    // Toggle #type_of_product_other visibility
    $('#type_of_product').on('change', function () {
        if ($(this).val() === 'Other') {
            $('#type_of_product_other').show().prop('required', true);
        } else {
            $('#type_of_product_other').hide().val('').prop('required', false);
        }
    });

    // Toggle #type_of_product_other visibility
    $('#uom').on('change', function () {
        if ($(this).val() === 'Other') {
            $('#uom_other').show().prop('required', true);
        } else {
            $('#uom_other').hide().val('').prop('required', false);
        }
    });
    
    // Toggle .recurringServiceInfo visibility
    $('input[name="service_type"]').on('change', function () {
        if ($(this).val() === 'recurring') {
            $('.recurringServiceInfo').show();
        } else {
            $('.recurringServiceInfo').hide().find('input, select').val('');
        }
    });

    // Initialize Flatpickr for date fields
    flatpickr('#completed_date', {
        dateFormat: 'Y-m-d',
        disableMobile: true, // Ensure flatpickr UI is used on mobile
    });    

    flatpickr('#recurrence_end_date', {
        minDate: new Date().fp_incr(7),
        dateFormat: 'Y-m-d',
        disableMobile: true, // Ensure flatpickr UI is used on mobile
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates[0] < new Date().fp_incr(7)) {
                alert('Please select a recurrence end date that is at least one week from today.');
                instance.clear();
            }
        }
    });

    setTimeout(function() {
        $(".flatpickr").removeAttr("readonly");
    }, 500);

});
</script>

