

<div class="newUser AccountInfo">

	<h4>Welcome to My.Aghawk Portal</h4>
	
	<p><strong>Let's get started!</strong> Please provide the following information:</p>
	
	<form method="POST" action="continueRegistration">
		<input type="hidden" name="action" value="continueRegistration" />		
		
		<div class="userRegistrationSection">		
			<h5>Customer Information:</h5>
			<input type="text" name="business_name" id="business_name" placeholder="Customer/Business Name:" required>
			<input type="tel" name="business_phone" id="business_phone" placeholder="Main Phone:" required>
			<select name="acreage_size" id="acreage_size" required>
				<option value="">Total Approximate Acreage:</option>
				<option value="Under 50">Under 50</option>
				<option value="50-200">50-200</option>
				<option value="201-500">201-500</option>
				<option value="Above 500">Above 500</option>
			</select>
			<select name="crop_category" id="crop_category" required>
				<option value="">Crop Category:</option>
				<option value="Row Crops">Row Crops</option>
				<option value="Orchard">Orchard</option>
				<option value="Vineyard">Vineyard</option>
				<option value="Pasture">Pasture</option>
				<option value="Grass field">Grass field</option>
				<option value="Mix">Mix</option>
			</select>
			<span id="crop_mix_notes_row" style="display:none;">
				<input type="text" name="crop_mix_notes" id="crop_mix_notes" placeholder="Crop Mix:" value="<?php echo htmlspecialchars($account['crop_mix_notes']); ?>">
			</span>
		</div><!--//.userRegistrationSection-->
		
		<div class="userRegistrationSection">
			<h5>Mailing Address:</h5>
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
			<br />
			<p>Is billing address same as mailing address?<br />
				<label><input type="radio" name="sameBillingingAddress" value="yes" required> Yes</label> &nbsp;
				<label><input type="radio" name="sameBillingingAddress" value="no" required> No</label>
			</p>
			
			<div id="billingAddressFields" style="display: none;">
				<h5>Billing Address:</h5>
				<input type="text" name="billing_address" id="billing_address" placeholder="Street Address:" required>
				<input type="text" name="billing_city" id="billing_city" placeholder="City:" required>
				<select name="state" id="billing_state" id="billing_state" required>
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
				<input type="text" name="billing_zip" id="billing_zip" placeholder="Zip Code:" required>
			</div><!--//#billingAddressFields-->
			
			<p><button type="submit">Continue &raquo;</button></p>
			
		</div><!--//.userRegistrationSection-->
	</form>

</div><!--//.newUser.AccountInfo-->


<script>
    $(document).ready(function() {
				
        $('#business_phone').mask('000-000-0000'); // Format: (123) 456-7890

        // Initialize crop mix notes visibility
        const initialCropCategory = $('#crop_category').val();
        if (initialCropCategory === 'Mix') {
            $('#crop_mix_notes_row').show();
        } else {
            $('#crop_mix_notes_row').hide();
        }
		
        // Show/hide billing address fields based on the "sameBillingingAddress" selection
        $('input[name="sameBillingingAddress"]').on('change', function() {
            const selectedValue = $('input[name="sameBillingingAddress"]:checked').val();
            if (selectedValue === 'no') {
                $('#billingAddressFields').show();
                $('input[name="billing_address"]').val('');
                $('input[name="billing_city"]').val('');
                $('select[name="state"][id="billing_state"]').val('');
                $('input[name="billing_zip"]').val('');
            } else {
                $('#billingAddressFields').hide();
                // Automatically populate billing address with mailing address
                $('input[name="billing_address"]').val($('input[name="street_address"]').val());
                $('input[name="billing_city"]').val($('input[name="city"]').val());
                $('select[name="state"][id="billing_state"]').val($('#state').val());
                $('input[name="billing_zip"]').val($('input[name="zip"]').val());
            }
        });

        // Initialize visibility and populate fields on page load
        const selectedValue = $('input[name="sameBillingingAddress"]:checked').val();
        if (selectedValue === 'no') {
            $('#billingAddressFields').show();
        } else {
            $('#billingAddressFields').hide();
        }

        // Crop mix notes toggle functionality
        $('#crop_category').on('change', function() {
            const cropCategory = $(this).val();
            if (cropCategory === 'Mix') {
                $('#crop_mix_notes_row').show();
				$('#crop_mix_notes').attr('required', true);
            } else {
                $('#crop_mix_notes_row').hide();
                $('#crop_mix_notes').val('');
				$('#crop_mix_notes').removeAttr('required');
            }
        });

    });
</script>


