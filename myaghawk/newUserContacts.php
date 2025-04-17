<div class="newUser AccountInfo">

    <h4>Welcome to My.Aghawk Portal</h4>

    <p>One more step for account registration: <strong>Please enter account contact(s) below.</strong></p>

    <form method="POST">
        <input type="hidden" name="action" value="registerNewAccount" />
		
		<input type="hidden" name="reg_business_name" value="<?php echo $_SESSION['reg_business_name']; ?>">
		<input type="hidden" name="reg_business_phone" value="<?php echo $_SESSION['reg_business_phone']; ?>">
		<input type="hidden" name="reg_acreage_size" value="<?php echo $_SESSION['reg_acreage_size']; ?>">
		<input type="hidden" name="reg_crop_category" value="<?php echo $_SESSION['reg_crop_category']; ?>">
		<input type="hidden" name="reg_crop_mix_notes" value="<?php echo $_SESSION['reg_crop_mix_notes']; ?>">
		<input type="hidden" name="reg_street_address" value="<?php echo $_SESSION['reg_street_address']; ?>">
		<input type="hidden" name="reg_city" value="<?php echo $_SESSION['reg_city']; ?>">
		<input type="hidden" name="reg_state" value="<?php echo $_SESSION['reg_state']; ?>">
		<input type="hidden" name="reg_zip" value="<?php echo $_SESSION['reg_zip']; ?>">
		<input type="hidden" name="reg_sameBillingingAddress" value="<?php echo $_SESSION['reg_sameBillingingAddress']; ?>">
		<input type="hidden" name="reg_billing_address" value="<?php echo $_SESSION['reg_billing_address']; ?>">
		<input type="hidden" name="reg_billing_city" value="<?php echo $_SESSION['reg_billing_city']; ?>">
		<input type="hidden" name="reg_billing_zip" value="<?php echo $_SESSION['reg_billing_zip']; ?>">
		

        <div class="userRegistrationSection">
            <div class="contactSection">
				<p><input type="text" name="first_name[]" placeholder="First Name:" required>
					<input type="text" name="last_name[]" placeholder="Last Name:" required>
					<input type="email" name="contact_email[]" placeholder="Email:" required>
					<input type="text" name="phone[]" class="phone" placeholder="Phone:" required>
				</p>				
				<p><select name="role[]" required>
						<option value="">Select User Role</option>
						<option value="Services Contact">Services Contact</option>
						<option value="Billing Contact">Billing Contact</option>
					</select>
					<input type="password" name="password[]" class="password-input" placeholder="Create a Password:" required> 
					<span class="passwordStrengthWrap">
						Password Strength: 
						<span class="password-strength-bar"> 
							<span class="strength-section"></span>
							<span class="strength-section"></span>
							<span class="strength-section"></span>
							<span class="strength-section"></span>
							<span class="strength-section"></span>
						</span>
						<span class="password-hint"></span>
					</span><!--.passwordStrengthWrap-->
				</p>
            </div>

            <div class="additionalContacts"></div>

            <p>Would you like to add another contact? <button type="button" id="addAnother">Yes, Add Another</button></p>
			
			<p>Would you like to proceed with property details now?<br />
				<label><input type="radio" name="continueToPropertyDetails" value="yes" required> Yes</label> &nbsp;
				<label><input type="radio" name="continueToPropertyDetails" value="no" required> No</label>
			</p>

            <p><button type="submit" id="submitBtn" style="display: none;">Save Progress &raquo;</button></p>

        </div><!--//.userRegistrationSection-->
    </form>

</div><!--//.newUser.AccountInfo-->

<script>
    $(document).ready(function() {
        // Phone number mask
        $('.phone').mask('000-000-0000');

        // Function to add new contact inputs
        $('#addAnother').on('click', function(e) {
            e.preventDefault(); // Prevent form submission

            // Template for additional contact
            const newContact = `
            <div class="contactSection additionalContact">
                <p><input type="text" name="first_name[]" placeholder="First Name:" required>
                    <input type="text" name="last_name[]" placeholder="Last Name:" required>
                    <input type="email" name="contact_email[]" placeholder="Email:" required>
                    <input type="text" name="phone[]" class="phone" placeholder="Phone:" required>
                </p>
                <p><select name="role[]" required>
                        <option value="">Select User Role</option>
                        <option value="Services Contact">Services Contact</option>
                        <option value="Billing Contact">Billing Contact</option>
                    </select>
                    <input type="password" name="password[]" class="password-input" placeholder="Create a Password:" required>
					<span class="passwordStrengthWrap">
						Password Strength: 
						<span class="password-strength-bar"> 
							<span class="strength-section"></span>
							<span class="strength-section"></span>
							<span class="strength-section"></span>
							<span class="strength-section"></span>
							<span class="strength-section"></span>
						</span>
						<span class="password-hint"></span>
					</span><!--.passwordStrengthWrap-->
                </p>
                <button type="button" class="removeContact">Remove</button>
            </div>
            `;

            // Append the new contact section
            $('.additionalContacts').append(newContact);

            // Reinitialize phone mask for dynamically added inputs
            $('.phone').mask('000-000-0000');
        });
		
		
		// Event delegation for removing contact sections
		$(document).on('click', '.removeContact', function() {
			$(this).closest('.contactSection').remove();
		});
		
		
		// CHECK FOR REGISTERED EMAILS
		$(document).on('input', 'input[name="contact_email[]"]', function () {
			const emailField = $(this);
			const email = emailField.val().trim();

			if (email.length > 0) {
				fetch(`_check_registered_email.php?contact_email=${encodeURIComponent(email)}`)
					.then((response) => response.json())
					.then((data) => {
						const errorLabel = emailField.siblings('.email-error');
						if (data.exists) {
							if (!errorLabel.length) {
								emailField.after('<span class="email-error" style="color: red;">Email already in use!</span>');
							}
						} else {
							errorLabel.remove();
						}
					})
					.catch((error) => {
						console.error('Error checking email:', error);
					});
			} else {
				emailField.siblings('.email-error').remove();
			}
		});
		

        // Password strength functionality for all password inputs (existing and new)
		$(document).on('input', '.password-input', function () {
			const password = $(this).val();
			const strength = evaluatePasswordStrength(password);

			// Update the strength bar
			const $wrapper = $(this).closest('.contactSection').find('.passwordStrengthWrap');
			const $barSections = $wrapper.find('.password-strength-bar').children('.strength-section');
			$barSections.removeClass('strength-weak strength-fair strength-good strength-strong strength-very-strong');

			for (let i = 0; i < strength.level; i++) {
				$barSections.eq(i).addClass(strength.class);
			}

			// Show hint text
			$wrapper.find('.password-hint').text(strength.hint);
		});

        // Evaluate password strength function
		function evaluatePasswordStrength(password) {
			let score = 0;
			let hint = '';
			let strengthClass = '';

			if (password.length >= 10) score++;
			if (/[a-z]/.test(password)) score++;
			if (/[A-Z]/.test(password)) score++;
			if (/[0-9]/.test(password)) score++;
			if (/[^a-zA-Z0-9]/.test(password)) score++;

			switch (score) {
				case 1:
					hint = 'Very Weak';
					strengthClass = 'strength-weak';
					break;
				case 2:
					hint = 'Weak';
					strengthClass = 'strength-fair';
					break;
				case 3:
					hint = 'Fair';
					strengthClass = 'strength-good';
					break;
				case 4:
					hint = 'Strong';
					strengthClass = 'strength-strong';
					break;
				case 5:
					hint = 'Very Strong';
					strengthClass = 'strength-very-strong';
					break;
				default:
					hint = 'Too Short';
					strengthClass = 'strength-weak';
			}

			return { level: score, hint: hint, class: strengthClass };
		}
		

        // Validate form on submit
        $('form').on('submit', function(e) {
			
			//EMAIL CHECKING
			let hasError = false;
			$('input[name="contact_email[]"]').each(function () {
				if ($(this).siblings('.email-error').length > 0) {
					hasError = true;
				}
			});
			if (hasError) {
				e.preventDefault();
				alert('Please fix duplicate email errors before submitting.');
			}
			
			//PASSWORD CHECKING
            let isValid = true;
            // Check all password fields
            $('.password-input').each(function() {
                const password = $(this).val();
                const strength = evaluatePasswordStrength(password);
                if (strength.level < 4) { // Require at least "Strong"
                    isValid = false;
                    $(this).siblings('.password-hint').text('Password must be Strong or Very Strong.');
                }
            });

            // If any password is invalid, prevent form submission
            if (!isValid) {
                e.preventDefault();
                alert('Please ensure all passwords are Strong or Very Strong before submitting.');
            }
        });
		
        // Change submit button text based on continueToPropertyDetails value
        $('input[name="continueToPropertyDetails"]').on('change', function() {
            const selectedValue = $('input[name="continueToPropertyDetails"]:checked').val();
            if (selectedValue === 'no') {
                $('#submitBtn').html('Register Account &raquo;').show();
            } else {
                $('#submitBtn').html('Register &amp; Continue &raquo;').show();
            }
        });
    });
</script>

