<?php
$token = $_GET['token'] ?? null;

?>


<div class="loginForms">

    <?php if ($token): ?>
        <!-- Reset Password Form -->
        <h4>Reset Your Password</h4>
        <form method="POST" action="password_reset">
            <input type="hidden" name="action" value="resetPassword">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required><br />
            <button type="submit">Reset Password</button>
        </form>
    <?php else: ?>
        <!-- Forgot Password Form -->
        <h4>Forgot Your Password?</h4>
        <form method="POST" action="password_reset">
            <input type="hidden" name="action" value="forgotPassword">
            <input type="email" name="email" id="email" placeholder="Enter Your Email:" required><br />
            <button type="submit">Submit</button>
			
			<p><a href="./?logout"><strong>Return Home</strong></a></p>
        </form>
    <?php endif; ?>
	
	
</div><!--//.loginForms-->

<script>
    $(document).ready(function() {
		$('#hdrLogo a').attr('href','./?logout');
	});
</script>