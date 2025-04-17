<?php

// Get account ID from the session
if (!isset($_SESSION['account_id'])) {
    die('Account ID is required.');
}
$account_id = intval($_SESSION['account_id']);

// Ensure user_id exists in URL
if (!isset($_GET['account_user_id'])) {
    die('Account User ID is required.');
}

$account_user_id = intval($_GET['account_user_id']);

// Prepare & Execute Query
$sql_account_user = "SELECT * FROM Accounts_Users WHERE account_id = ? AND account_user_id = ?";
$stmt_account_user = $conn->prepare($sql_account_user);
if (!$stmt_account_user) {
    die("SQL Error: " . $conn->error); // Debugging output for SQL error
}

$stmt_account_user->bind_param("ii", $account_id, $account_user_id);
$stmt_account_user->execute();
$result_account_user = $stmt_account_user->get_result();

// If user not found, destroy session & redirect
if ($result_account_user->num_rows == 0) {
    session_unset();
    session_destroy();
    
    session_start(); // Restart session to store display message
    $_SESSION['displayMsg'] = 'Account not found.';
    
    header("Location: https://my.aghawkdynamics.com/");
    exit(); // Ensure script stops execution after redirect
}

// Fetch user data
$account_user = $result_account_user->fetch_assoc();
?>


<h3>Edit User</h3>

<p><hr /></p>


<section class="userSection Profile">

    <form action="profile" method="POST">
        <input type="hidden" name="action" value="updateUser" />
        <input type="hidden" name="account_user_id" value="<?= $account_user_id; ?>" />
        <input type="hidden" name="account_id" value="<?= $_SESSION['account_id']; ?>" />
        
        <table class="blockTable">
        <thead>
            <tr>
                <th colspan="2">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>First Name:</td>
                <td><input type="text" name="contact_first_name" id="contact_first_name" value="<?= $account_user['contact_first_name']; ?>" placeholder="First Name:" /></td>
            </tr>
            <tr>
                <td>Last Phone:</td>
                <td><input type="text" name="contact_last_name" id="contact_last_name" value="<?= $account_user['contact_last_name']; ?>" placeholder="Last Name:" /></td>
            </tr>
            <tr>
                <td>Email Address:</td>
                <td><input type="text" name="contact_email" id="contact_email" value="<?= $account_user['contact_email']; ?>" placeholder="Email Address:" /></td>
            </tr>
            <tr>
                <td>Phone:</td>
                <td><input type="text" name="phone" id="phone" value="<?= $account_user['phone']; ?>" placeholder="Phone:" /></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="text" name="password" id="password" class="password-input" value="" placeholder="New Password:" /> <em>(leave empty to remain unchanged)</em>
                <br />
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
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type="submit" id="submitBtn">Update User</button> &nbsp; 
                    <a href="profile">Cancel &raquo;</a>
                </td>
            </tr>
            </tbody>
        </table>    
    </form>
    
</section>

<p>&nbsp;</p>


<script>
$(document).ready(function() {

    // Password strength functionality for all password inputs (existing and new)
    $(document).on('input', '.password-input', function () {
        const password = $(this).val();
        const strength = evaluatePasswordStrength(password);

        // Update the strength bar
        const $wrapper = $(this).closest('.userSection').find('.passwordStrengthWrap');
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
        let isValid = true;

        $('.password-input').each(function() {
            const password = $(this).val();

            // Only validate if a new password is entered
            if (password.length > 0) {
                const strength = evaluatePasswordStrength(password);
                if (strength.level < 4) { // Require at least "Strong"
                    isValid = false;
                    $(this).siblings('.password-hint').text('Password must be Strong or Very Strong.');
                }
            }
        });

        // If any password is invalid, prevent form submission
        if (!isValid) {
            e.preventDefault();
            alert('Please ensure the new password is Strong or Very Strong before submitting.');
        }
    });
});

</script>
