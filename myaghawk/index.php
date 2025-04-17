<? // MyAgHawk Customer Portal

include_once( '_inc.php' );

?>
<!doctype html>
<html>
<head>
	
	<title>My.AgHawk Portal</title>
	
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
	
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&family=Recursive:wght@300..1000&display=swap" rel="stylesheet">
	
	<link rel='stylesheet' id='megamenu-css' href='https://aghawkdynamics.com/wp-content/uploads/maxmegamenu/style.css?ver=7db994' media='all' />
	
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/style.css?t=<?php echo time(); ?>" />

	<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/favicon.png?t=<?php echo time(); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles_custom.css?t=<?php echo time(); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles_navigation.css?t=<?php echo time(); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles_responsive.css?t=<?php echo time(); ?>" />
	
	<script src="https://kit.fontawesome.com/cec9e0a1f4.js" crossorigin="anonymous"></script>
	<link rel="stylesheet" type="text/css" href="styles_custom.css?t=<?php echo time(); ?>" />
	<link rel="stylesheet" type="text/css" href="styles_responsive.css?t=<?php echo time(); ?>" />
	
	<!-- datepicker -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	
	<!-- attachments -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
	
</head>

	
<body>
<header id="masthead" class="site-header <? if(!isset($_SESSION['user_id'])) { echo "notLoggedIn"; } ?> " role="banner">
	<div class="pad">
		<h1 id="hdrLogo" class="site-title">
			<a href="./">
				<img src="images/admin-logo.png?v=<?= time(); ?>" border="0">
				<div class="text">
					My.AG<span>HAWK</span>
					<span class="portal">Portal</span>
				</div>
			</a>
		</h1>
		
		<? if(isset($_SESSION['account_user_id'])) { ?>
		
			<div class="hdrUser">			
				Welcome, <? echo $_SESSION['contact_first_name']; ?> | <a href="?logout">Logout</a>
			</div>

			<div id="navBar">
				<nav id="site-navigation" class="primary-navigation" aria-label="Primary menu">
					<div class="menu-button-container">
						<button id="primary-mobile-menu" class="button" aria-controls="primary-menu-list" aria-expanded="false"> <span class="dropdown-icon open">Menu
						<svg class="svg-icon" width="24" height="24" aria-hidden="true" role="img" focusable="false" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M4.5 6H19.5V7.5H4.5V6ZM4.5 12H19.5V13.5H4.5V12ZM19.5 18H4.5V19.5H19.5V18Z" fill="currentColor"></path>
						</svg>
						</span>
							<span class="dropdown-icon close">Close
						<svg class="svg-icon" width="24" height="24" aria-hidden="true" role="img" focusable="false" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M12 10.9394L5.53033 4.46973L4.46967 5.53039L10.9393 12.0001L4.46967 18.4697L5.53033 19.5304L12 13.0607L18.4697 19.5304L19.5303 18.4697L13.0607 12.0001L19.5303 5.53039L18.4697 4.46973L12 10.9394Z" fill="currentColor"></path>
						</svg>
						</span>
						</button>
						<!-- #primary-mobile-menu --> 
					</div>
					<!-- .menu-button-container -->

					<div id="mega-menu-wrap-primary" class="mega-menu-wrap">
						<div class="mega-menu-toggle">
							<div class="mega-toggle-blocks-left"></div>
							<div class="mega-toggle-blocks-center"></div>
							<div class="mega-toggle-blocks-right">
								<div class="mega-toggle-block mega-menu-toggle-animated-block mega-toggle-block-0" id="mega-toggle-block-0">
									<button aria-label="Toggle Menu" class="mega-toggle-animated mega-toggle-animated-slider" type="button" aria-expanded="false"> <span class="mega-toggle-animated-box"> <span class="mega-toggle-animated-inner"></span> </span> </button>
								</div>
							</div>
						</div>
						<ul id="mega-menu-primary" class="mega-menu max-mega-menu mega-menu-horizontal" data-event="hover_intent" data-effect="fade_up" data-effect-speed="200" data-effect-mobile="disabled" data-effect-speed-mobile="0" data-mobile-force-width="false" data-second-click="go" data-document-click="collapse" data-vertical-behaviour="standard" data-breakpoint="890" data-unbind="true" data-mobile-state="collapse_all" data-hover-intent-timeout="300" data-hover-intent-interval="100">
							<li class="mega-menu-item mega-menu-flyout"><a class="mega-menu-link" href="profile">My Profile</a></li>

							<li class="mega-menu-item  mega-menu-item-has-children  mega-menu-flyout"><a class="mega-menu-link" href="parcels" aria-haspopup="true" aria-expanded="false">
                                Parcels<span class="mega-indicator" data-has-click-event="true"></span></a>
								<ul class="mega-sub-menu">
									<li class="mega-menu-item "><a class="mega-menu-link" href="parcel_add">Add Parcel</a></li>
								</ul>
							</li>
                            <li class="mega-menu-item  mega-menu-item-has-children  mega-menu-flyout"><a class="mega-menu-link" href="blocks" aria-haspopup="true" aria-expanded="false">
                                Blocks<span class="mega-indicator" data-has-click-event="true"></span></a>
								<ul class="mega-sub-menu">
									<li class="mega-menu-item "><a class="mega-menu-link" href="block_add">Add Block</a></li>
								</ul>
							</li>

							<li class="mega-menu-item  mega-menu-item-has-children  mega-menu-flyout"><a class="mega-menu-link" href="service_requests" aria-haspopup="true" aria-expanded="false">
                                Activity<span class="mega-indicator" data-has-click-event="true"></span></a>
								<ul class="mega-sub-menu">
									<li class="mega-menu-item "><a class="mega-menu-link" href="service_request_add">Request Service</a></li>
									<li style="display: none;" class="mega-menu-item "><a class="mega-menu-link" href="recurring_services">Recurring Service Schedules</a></li>
									<li class="mega-menu-item "><a class="mega-menu-link" href="self_tracking">Self-Tracking</a></li>
									<li style="display: none;" class="mega-menu-item "><a class="mega-menu-link" href="self_tracking_schedules">Self-Tracking Schedules</a></li>
								</ul>
							</li>

						</ul>
					</div>
				</nav>
				<!-- #site-navigation --> 
			</div>
			<!--//#navBar-->
		
		<? } //end if if(isset($_SESSION['first_name'])) ?>
		
	</div>
	<!--//.pad--> 
	
</header>
	
<div id="page" class="site <?php echo $post_slug; ?>">
	<div id="content" class="site-content">
		
		
		<div class="pad">
			<div class="contentArea">
				
				<?php 
					if(isset($_SESSION['displayMsg'])) { 
				?>
						<div class="displayMsg"><?php echo $_SESSION['displayMsg']; ?></div>
						<script>
							$(document).ready(function() {								
								if ($('.displayMsg').length) {// Check if the displayMsg element exists									
									$('.displayMsg').delay(8000).fadeOut(1000, function() {// Wait 8 seconds, then fade out over 1 second and remove the element
										$(this).remove();
									});
								}
							});
						</script>
				<?php
						unset($_SESSION['displayMsg']);
					} //end displayMsg
				?>
				
			<?php 
				if(!isset($_SESSION['user_status'])) {
					if($_SESSION['currentScreen']=='password_reset') {
						include_once 'password_reset.php';
					} else {
						include_once 'login.php';						
					}
				} else {
					include_once($_SESSION['currentScreen'].'.php');
				}
			?>
				
			</div><!--//.contentArea-->
		
		</div><!--//.pad-->	
		
		
		

		<div id="Footer">
			<div class="pad">

				<div class="ftrContactInfo">
					<h4>Contact Us</h4>
					<a href="tel:509-449-8989">509-449-8989</a><br />
					PO Box 64<br/>
					Manson, WA. 98831<br />
					<a href="mailto:chris@aghawkdynamics.com">chris@aghawkdynamics.com</a><br />
				</div>

				<div class="rightCol">

					<p class="Orbitron">AG<span>HAWK</span><br />
					<span class="smaller">DYNAMICS</span></p>
										
					<div class="social">
						<a target="_blank" href="https://www.facebook.com/profile.php?id=61566182047013"><img src="<?php echo get_template_directory_uri(); ?>/images/ftrSocialFB.png?v=3" /></a>
						<a target="_blank" href="https://www.instagram.com/aghawk.dynamics/"><img src="<?php echo get_template_directory_uri(); ?>/images/ftrSocialIG.png?v=3" /></a>
						<a target="_blank" href="https://www.youtube.com/@Aghawk.Dynamics"><img src="<?php echo get_template_directory_uri(); ?>/images/ftrSocialYT.png?v=3" /></a>
						<a target="_blank" href="https://www.x.com/AghawkDynamics"><img src="<?php echo get_template_directory_uri(); ?>/images/ftrSocialX.png?v=3" /></a>
						<a target="_blank" href="https://www.tiktok.com/@aghawkdynamics"><img src="<?php echo get_template_directory_uri(); ?>/images/ftrSocialTT.png" /></a>
					</div>
				</div>

				<br class="Clear" />

				<div id="siteCredits">
					&copy;<?php echo date("Y"); ?> AgHawk Dynamics, Inc.
					<a class="nwwebdev" target="_blank" href="https://nwwebdev.com" title="Web Application Design &amp; Development">Development: Northwest Web Dev</a>
				</div>
			</div><!--//.pad-->
		</div> <!--//Footer-->


</div><!-- #page .site -->

	
	<script>document.body.classList.remove("no-js");</script>
	<script>
		if ( -1 !== navigator.userAgent.indexOf( 'MSIE' ) || -1 !== navigator.appVersion.indexOf( 'Trident/' ) ) {
			document.body.classList.add( 'is-IE' );
		}
	</script>
	<script src="https://aghawkdynamics.com/wp-includes/js/dist/hooks.min.js?ver=2810c76e705dd1a53b18" id="wp-hooks-js"></script>
	<script src="https://aghawkdynamics.com/wp-includes/js/dist/i18n.min.js?ver=5e580eb46a90c2b997e6" id="wp-i18n-js"></script>
	<script id="wp-i18n-js-after">
		wp.i18n.setLocaleData( { 'text direction\u0004ltr': [ 'ltr' ] } );
	</script>
	<script src="https://aghawkdynamics.com/wp-content/plugins/contact-form-7/includes/swv/js/index.js?ver=5.9.8" id="swv-js"></script>
	<script id="contact-form-7-js-extra">
		var wpcf7 = {"api":{"root":"https:\/\/aghawkdynamics.com\/wp-json\/","namespace":"contact-form-7\/v1"}};
	</script>
	<script src="https://aghawkdynamics.com/wp-content/plugins/contact-form-7/includes/js/index.js?ver=5.9.8" id="contact-form-7-js"></script>
	<script src="https://aghawkdynamics.com/wp-content/themes/aghawk/assets/js/responsive-embeds.js?ver=1.0" id="twenty-twenty-one-responsive-embeds-script-js"></script>
	<script src="https://www.google.com/recaptcha/api.js?render=6Lc6C1EqAAAAAAD8KtYnzHZjoueSUGUGcrKx3YZy&amp;ver=3.0" id="google-recaptcha-js"></script>
	<script src="https://aghawkdynamics.com/wp-includes/js/dist/vendor/wp-polyfill.min.js?ver=3.15.0" id="wp-polyfill-js"></script>
	<script id="wpcf7-recaptcha-js-extra">
		var wpcf7_recaptcha = {"sitekey":"6Lc6C1EqAAAAAAD8KtYnzHZjoueSUGUGcrKx3YZy","actions":{"homepage":"homepage","contactform":"contactform"}};
	</script>
	<script src="https://aghawkdynamics.com/wp-content/plugins/contact-form-7/modules/recaptcha/index.js?ver=5.9.8" id="wpcf7-recaptcha-js"></script>
	<script src="https://aghawkdynamics.com/wp-includes/js/hoverIntent.min.js?ver=1.10.2" id="hoverIntent-js"></script>
	<script id="megamenu-js-extra">
		var megamenu = {"timeout":"300","interval":"100"};
	</script>
	<script src="https://aghawkdynamics.com/wp-content/plugins/megamenu/js/maxmegamenu.js?ver=3.3.1.2" id="megamenu-js"></script>


<?php
	/**/
	if ($_SERVER['REMOTE_ADDR'] == '96.46.17.70') {
		echo '<pre>';
		print_r($_SESSION); // Display all session data
		echo '</pre>';
	}
	/**/
?>
	
</body>
</html>


