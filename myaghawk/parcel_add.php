<?php

if (!isset($_SESSION['account_id'])) {
	session_unset();
	session_destroy();
	session_start();
    $_SESSION['displayMsg'] = 'Account not found.';
	header("Location:https://my.aghawkdynamics.com/");
}
$account_id = intval($_SESSION['account_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    addParcelAndBlocks($account_id, $_POST);
}
?>

<section class="adminSection">
    <h3>Add New Parcel:</h3>
    
    <p><hr /></p>

    <form method="POST" action="parcels">
		<input type="hidden" name="account_id" value="<?php echo $_SESSION['account_id']; ?>" />
		<input type="hidden" name="action" value="addParcelAndBlocks" />
		
		<p>
			<input type="text" name="nickname" id="nickname" placeholder="Parcel Nickname:" required> 
			<input type="text" name="street_address" id="street_address" placeholder="Street Address:" required> 
			<input type="text" name="city" id="city" placeholder="City:" required>
			<select name="state" id="state" required>
				<option value="">State:</option>
				<option value="AL">Alabama</option>
				<option value="AK">Alaska</option>
				<option value="AZ">Arizona</option>
				<option value="AR">Arkansas</option>
				<option value="CA">California</option>
				<option value="CO">Colorado</option>
				<option value="CT">Connecticut</option>
				<option value="DE">Delaware</option>
				<option value="FL">Florida</option>
				<option value="GA">Georgia</option>
				<option value="HI">Hawaii</option>
				<option value="ID">Idaho</option>
				<option value="IL">Illinois</option>
				<option value="IN">Indiana</option>
				<option value="IA">Iowa</option>
				<option value="KS">Kansas</option>
				<option value="KY">Kentucky</option>
				<option value="LA">Louisiana</option>
				<option value="ME">Maine</option>
				<option value="MD">Maryland</option>
				<option value="MA">Massachusetts</option>
				<option value="MI">Michigan</option>
				<option value="MN">Minnesota</option>
				<option value="MS">Mississippi</option>
				<option value="MO">Missouri</option>
				<option value="MT">Montana</option>
				<option value="NE">Nebraska</option>
				<option value="NV">Nevada</option>
				<option value="NH">New Hampshire</option>
				<option value="NJ">New Jersey</option>
				<option value="NM">New Mexico</option>
				<option value="NY">New York</option>
				<option value="NC">North Carolina</option>
				<option value="ND">North Dakota</option>
				<option value="OH">Ohio</option>
				<option value="OK">Oklahoma</option>
				<option value="OR">Oregon</option>
				<option value="PA">Pennsylvania</option>
				<option value="RI">Rhode Island</option>
				<option value="SC">South Carolina</option>
				<option value="SD">South Dakota</option>
				<option value="TN">Tennessee</option>
				<option value="TX">Texas</option>
				<option value="UT">Utah</option>
				<option value="VT">Vermont</option>
				<option value="VA">Virginia</option>
				<option value="WA">Washington</option>
				<option value="WV">West Virginia</option>
				<option value="WI">Wisconsin</option>
				<option value="WY">Wyoming</option>
			</select>
			<input type="text" name="zip" id="zip" placeholder="Zip Code:" required> 
			<input type="number" name="acres" id="acres" placeholder="Approximate Acres:" required>
		</p>
		
		<p><strong>Block or Crop Information</strong></p>
		
		<p class="blockWrapper">
			<input type="text" name="block_nickname[]" class="block_nickname" placeholder="Block Nickname or Crop:" required>
			<select name="crop_category[]" class="crop_category" required>
				<option value="">Select Usage Type</option>
				<option value="Orchard">Orchard</option>
				<option value="Vineyard">Vineyard</option>
				<option value="Row Crops">Row Crops</option>
				<option value="Pasture">Pasture</option>
				<option value="Grass field">Grass field</option>
				<option value="Mix">Mix</option>
			</select>
			<span class="crop_mix_notes_row" style="display:none;">
				<input type="text" name="crop_mix_notes[]" class="crop_mix_notes" placeholder="Crop Mix:" value="">
			</span>            
			<input type="number" name="block_acres" id="block_acres" placeholder="Block Acres:" required>
			<br />
			<textarea name="notes[]" class="notes" rows="2" placeholder="Notes:"></textarea>
		</p>
		
        <div class="additionalBlocks"></div>
		
		<p>
			<strong>Does this parcel have additional blocks or crops?</strong><br />
			<button type="button" class="smallBtn" id="addAnother">Yes, Add Another</button> &nbsp;
			<button type="button" class="smallBtn" id="blocksComplete">No, that's everything</button>
		</p>
		
		<p id="createServiceRequests" style="display:none;">
			<strong>Would you like to request/schedule services now?</strong><br />
				<label><input type="radio" name="addServiceRequest" value="yes" required> Yes</label> &nbsp;
				<label><input type="radio" name="addServiceRequest" value="no" required> No</label>
		</p>
		
		<p><button type="submit" id="submitBtn" style="display: none;">Save Progress &raquo;</button></p>
    </form>
</section>


<script>
    $(document).ready(function() {
        
		// Event delegation for crop mix notes toggle functionality
		$(document).on('change', '.crop_category', function() {
			const $this = $(this); // Current dropdown
			const cropCategory = $this.val();
			const $notesRow = $this.closest('.blockWrapper').find('.crop_mix_notes_row'); // Find the related notes row
			const $notesInput = $notesRow.find('.crop_mix_notes'); // Find the related notes input
			if (cropCategory === 'Mix') {
				$notesRow.show();
				$notesInput.attr('required', true);
			} else {
				$notesRow.hide();
				$notesInput.val(''); // Clear input value
				$notesInput.removeAttr('required');
			}
		});


        // Function to add new block inputs
        $('#addAnother').on('click', function(e) {
            e.preventDefault(); // Prevent form submission
			
			$('input[name="addServiceRequest"]').prop('checked', false);
			$('#createServiceRequests, #submitBtn').hide();

            // Template for additional contact
            const newBlock = `
				<p class="blockWrapper">
					<input type="text" name="block_nickname[]" class="block_nickname" placeholder="Block Nickname or Crop:" required>
					<select name="crop_category[]" class="crop_category" required>
						<option value="">Select Usage Type</option>
						<option value="Orchard">Orchard</option>
						<option value="Vineyard">Vineyard</option>
						<option value="Row Crops">Row Crops</option>
						<option value="Pasture">Pasture</option>
						<option value="Grass field">Grass field</option>
						<option value="Mix">Mix</option>
					</select>
					<span class="crop_mix_notes_row" style="display:none;">
						<input type="text" name="crop_mix_notes[]" class="crop_mix_notes" placeholder="Crop Mix:" value="">
					</span>
			        <input type="number" name="block_acres" id="block_acres" placeholder="Block Acres:" required>
					<br />
					<textarea name="notes[]" class="notes" rows="2" placeholder="Notes:"></textarea>
					<button type="button" class="smallBtn removeBlock">Remove</button>
				</p>
            `;

            // Append the new contact section
            $('.additionalBlocks').append(newBlock);

        });
		
		
		// Event delegation for removing additional block sections
		$(document).on('click', '.removeBlock', function() {
			$(this).closest('.blockWrapper').remove();
		});
		

        $('#blocksComplete').on('click', function(e) {
            e.preventDefault(); // Prevent form submission
			$('#createServiceRequests').show();
		});
		
        // Change submit button text based on continueToPropertyDetails value
        $('input[name="addServiceRequest"]').on('change', function() {
            const selectedValue = $('input[name="addServiceRequest"]:checked').val();
            if (selectedValue === 'no') {
                $('#submitBtn').html('Save Parcel &amp; Block Information &raquo;').show();
				$('form').attr('action', 'parcels');
            } else {
                $('#submitBtn').html('Save &amp; Continue &raquo;').show();
				$('form').attr('action', 'service_request_add');
            }
        });
    });
</script>

