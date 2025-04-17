<?php
if ($_SERVER['REMOTE_ADDR'] == '96.46.17.70') {
	$loginEmail = "john.doe@droneworks.com";
	$loginPass = "aghawk2024";
}
?>

<div class="loginForms">

	<h4>Login</h4>

	<form method="POST">
		<input type="hidden" name="action" value="login" />
		<input type="email" name="email" id="loginEmail" required placeholder="Email:" value="<? echo $loginEmail; ?>"><br />
		<input type="password" name="password" id="loginPass" required placeholder="Password:" value="<? echo $loginPass; ?>"><br />
		
		<div class="links">
			<a class="floatLeft" href="password_reset"><strong>Forgot Password?</strong></a>
			
			<a class="floatRight" href="signup"><strong>Sign Up</strong></a>
			
		</div>
		
		<button type="submit">Login</button>
	</form>
	
	<?php if($loginError) { ?>
		<div class="loginError"><br>
			<?php echo $loginError; ?>
		</div>
	<?php } ?>

</div><!--//.loginForms-->

