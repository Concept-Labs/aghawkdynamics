<?php
// Get account ID
$account_id = $_SESSION['account_id'];

?>
<div class="addServiceRequest">
    <h3>Add New Service Request</h3>

    <p><hr /></p>

    <form method="POST" action="service_requests">
		<input type="hidden" name="account_id" value="<?php echo $_SESSION['account_id']; ?>" />
		<input type="hidden" name="action" value="addServiceRequest" />	
			
		<p><strong>What location requires a service?</strong></p>
		<p>
			<select name="parcel_id" id="parcel_id" required disabled>
				<option value="">Select Parcel</option>
			</select>

			<select name="block_id" id="block_id" required disabled>
				<option value="">Select Block</option>
			</select>

			<select name="contact_id" id="contact_id" required disabled>
				<option value="">Select Contact</option>
			</select>		
		</p>
		
		<p><strong>What services are you looking for?</strong></p>
		<p>
			<select name="type_of_service" required>
				<option value="">Select Service Type</option>
				<option value="Spray">Spray</option>
				<option value="Spread">Spread</option>
				<option value="Analyze">Analyze</option>
				<option value="Drying">Drying</option>
			</select>

			<input type="text" name="reason_for_application" required placeholder="Reason for Application:">
			
			<input type="text" name="application_need_by_date" id="application_need_by_date" required placeholder="Service Need By Date:" class="flatpickr">
			
			<select name="urgent_request" id="urgent_request">
				<option value="">Urgent Request?</option>
				<option value="Yes">Yes</option>
				<option value="No">No</option>
			</select>
            
			<input type="text" name="urgent_request_date" id="urgent_request_date" placeholder="Urgent Need By Date:" class="flatpickr" style="display:none;">
            
		</p>
			
		<p><strong>Do you know what type of product needs to be applied?</strong><br />
			<label><input type="radio" name="knowsWhichProduct" value="yes" required> Yes</label> &nbsp;
			<label><input type="radio" name="knowsWhichProduct" value="no" required> No</label>
			
			<div class="knowsWhichProduct" style="display:none;">
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
				<input type="text" name="product_name" placeholder="Product Name:">
				
			</div><!--//.knowsWhichProduct-->
		</p>
		<p class="willProvideProduct"><strong>Will you be providing the product?</strong><br />
			<label><input type="radio" name="willProvideProduct" value="yes"> Yes</label> &nbsp;
			<label><input type="radio" name="willProvideProduct" value="no"> No</label>
		</p>

		<p class="preferredSupplier"><strong>Do you have preferred Supplier?</strong><br />
			<label><input type="radio" name="hasPreferredSupplier" value="yes"> Yes</label> &nbsp;
			<label><input type="radio" name="hasPreferredSupplier" value="no"> No</label>
		</p>
			
		<p class="ProductSupplierInfo" style="display:none;"><strong>Please provide Product Supplier Information:</strong><br />
			<input type="text" name="supplier_name" placeholder="Supplier Name:">
			<input type="text" name="supplier_contact_phone" id="supplier_contact_phone" placeholder="Supplier Contact Phone:">				
			<input type="text" name="supplier_contact_name" placeholder="Supplier Contact Name:">			
		</p>

		<p><strong>Is this a recurring service?</strong><br />
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
			
			<span class="disclaimerText">Disclaimer – we did research, and all recurring services should be paused October-March (unless specifically requested). 
			</span>
		</p>

		<p><button type="submit">Create Service Request</button></p>        
		
    </form>

</div><!--//.addServiceRequest-->

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
							`<option value="${contact.account_user_id}">${contact.contact_first_name} ${contact.contact_last_name}</option>`
						);
					});
					$('#contact_id').prop('disabled', false);
				})
				.catch(error => console.error('Error fetching contacts:', error));
		});
	} else {
		$('#contact_id').html('<option value="">Select Contact</option>').prop('disabled', true);
	}


    // Toggle #urgent_request_date visibility
    $('#urgent_request').on('change', function () {
        if ($(this).val() === 'Yes') {
            $('#urgent_request_date').show();
        } else {
            $('#urgent_request_date').hide().val('');
        }
    });
    

    // Toggle .knowsWhichProduct visibility
    $('input[name="knowsWhichProduct"]').on('change', function () {
        if ($(this).val() === 'yes') {
            $('.knowsWhichProduct').show();
			$('.willProvideProduct, .preferredSupplier').show();
        } else {
            $('.knowsWhichProduct').hide().find('input, select').val('');
			$('.willProvideProduct, .preferredSupplier').hide();
        }
    });

    // Toggle ProductSupplierInfo and preferredSupplier visibility based on willProvideProduct
    $('input[name="willProvideProduct"]').on('change', function () {
        if ($(this).val() === 'yes') {
            $('.ProductSupplierInfo').show();
            $('.preferredSupplier').hide().find('input').prop('checked', false);
        } else {
            $('.ProductSupplierInfo').hide().find('input').val('');
            $('.preferredSupplier').show();
        }
    });

    // Toggle ProductSupplierInfo visibility based on hasPreferredSupplier
    $('input[name="hasPreferredSupplier"]').on('change', function () {
        if ($(this).val() === 'yes') {
            $('.ProductSupplierInfo').show();
        } else {
            $('.ProductSupplierInfo').hide().find('input').val('');
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

    // Toggle #type_of_product_other visibility
    $('#type_of_product').on('change', function () {
        if ($(this).val() === 'Other') {
            $('#type_of_product_other').show().prop('required', true);
        } else {
            $('#type_of_product_other').hide().val('').prop('required', false);
        }
    });

    // Initialize Flatpickr for date fields
    flatpickr('#application_need_by_date', {
        minDate: new Date().fp_incr(7),
        dateFormat: 'Y-m-d',
        disableMobile: true, // Ensure flatpickr UI is used on mobile
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates[0] < new Date().fp_incr(7)) {
                alert('Please select a date that is at least one week from today.');
                instance.clear();
            }
        }
    });
    
    flatpickr('#urgent_request_date', {
        minDate: new Date().fp_incr(1),
        dateFormat: 'Y-m-d',
        disableMobile: true, // Ensure flatpickr UI is used on mobile
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates[0] < new Date().fp_incr(1)) {
                alert('Please select a date that is at least 24 hours from today.');
                instance.clear();
            }
        }
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

