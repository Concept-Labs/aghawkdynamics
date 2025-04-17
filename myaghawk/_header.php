<?

include_once('../admin/_inc.php');

?><!doctype html>
<html>
<head>
	
	
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-44BZ3KHVVG"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'G-44BZ3KHVVG');
	</script>
	
	<meta name="viewport" content="width=device-width, initial-scale=1" />
		
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&family=Recursive:wght@300..1000&display=swap" rel="stylesheet">

	
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/style.css?t=<?php echo time(); ?>" />
	
    <link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/favicon.png?t=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles_custom.css?t=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles_navigation.css?t=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles_home.css?t=<?php echo time(); ?>" />	
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles_responsive.css?t=<?php echo time(); ?>" />
    
</head>

<body>
	
<header id="masthead" class="site-header " role="banner">

    <div class="pad">
        <h1 id="hdrLogo" class="site-title">
            <a href="https://aghawkdynamics.com/" title="Agricultural Drone Applications" rel="home">
                <img src="<?php echo get_template_directory_uri(); ?>/images/logo-long.png?v=1729115406" border="0">
            </a>
        </h1>
            
		<div id="navBar">
			
	<nav id="site-navigation" class="primary-navigation" aria-label="Primary menu">
		<div class="menu-button-container">
			<button id="primary-mobile-menu" class="button" aria-controls="primary-menu-list" aria-expanded="false">
				<span class="dropdown-icon open">Menu					<svg class="svg-icon" width="24" height="24" aria-hidden="true" role="img" focusable="false" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.5 6H19.5V7.5H4.5V6ZM4.5 12H19.5V13.5H4.5V12ZM19.5 18H4.5V19.5H19.5V18Z" fill="currentColor"></path></svg>				</span>
				<span class="dropdown-icon close">Close					<svg class="svg-icon" width="24" height="24" aria-hidden="true" role="img" focusable="false" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 10.9394L5.53033 4.46973L4.46967 5.53039L10.9393 12.0001L4.46967 18.4697L5.53033 19.5304L12 13.0607L18.4697 19.5304L19.5303 18.4697L13.0607 12.0001L19.5303 5.53039L18.4697 4.46973L12 10.9394Z" fill="currentColor"></path></svg>				</span>
			</button><!-- #primary-mobile-menu -->
		</div><!-- .menu-button-container -->
		<div id="mega-menu-wrap-primary" class="mega-menu-wrap"><div class="mega-menu-toggle"><div class="mega-toggle-blocks-left"></div><div class="mega-toggle-blocks-center"></div><div class="mega-toggle-blocks-right"><div class="mega-toggle-block mega-menu-toggle-animated-block mega-toggle-block-0" id="mega-toggle-block-0"><button aria-label="Toggle Menu" class="mega-toggle-animated mega-toggle-animated-slider" type="button" aria-expanded="false">
                  <span class="mega-toggle-animated-box">
                    <span class="mega-toggle-animated-inner"></span>
                  </span>
                </button></div></div></div><ul id="mega-menu-primary" class="mega-menu max-mega-menu mega-menu-horizontal" data-event="hover_intent" data-effect="fade_up" data-effect-speed="200" data-effect-mobile="disabled" data-effect-speed-mobile="0" data-mobile-force-width="false" data-second-click="go" data-document-click="collapse" data-vertical-behaviour="standard" data-breakpoint="890" data-unbind="true" data-mobile-state="collapse_all" data-hover-intent-timeout="300" data-hover-intent-interval="100"><li class="mega-menu-item mega-menu-item-type-post_type mega-menu-item-object-page mega-menu-item-home mega-current-menu-item mega-page_item mega-page-item-2 mega-current_page_item mega-align-bottom-left mega-menu-flyout mega-menu-item-20" id="mega-menu-item-20"><a class="mega-menu-link" href="https://aghawkdynamics.com/" aria-current="page" tabindex="0">Home</a></li><li class="mega-menu-item mega-menu-item-type-post_type mega-menu-item-object-page mega-menu-item-has-children mega-align-bottom-left mega-menu-flyout mega-menu-item-23" id="mega-menu-item-23"><a class="mega-menu-link" href="https://aghawkdynamics.com/services/" aria-haspopup="true" aria-expanded="false" tabindex="0">Precision Services<span class="mega-indicator" data-has-click-event="true"></span></a>
<ul class="mega-sub-menu">
<li class="mega-menu-item mega-menu-item-type-post_type mega-menu-item-object-page mega-menu-item-30" id="mega-menu-item-30"><a class="mega-menu-link" href="https://aghawkdynamics.com/services/precision-spraying/">Precision Spraying</a></li><li class="mega-menu-item mega-menu-item-type-post_type mega-menu-item-object-page mega-menu-item-40" id="mega-menu-item-40"><a class="mega-menu-link" href="https://aghawkdynamics.com/services/precision-spreading/">Precision Spreading</a></li><li class="mega-menu-item mega-menu-item-type-post_type mega-menu-item-object-page mega-menu-item-29" id="mega-menu-item-29"><a class="mega-menu-link" href="https://aghawkdynamics.com/services/crop-analytics/">Crop Analytics</a></li><li class="mega-menu-item mega-menu-item-type-post_type mega-menu-item-object-page mega-menu-item-28" id="mega-menu-item-28"><a class="mega-menu-link" href="https://aghawkdynamics.com/services/aerial-mapping/">Aerial Mapping</a></li></ul>
</li><li class="mega-menu-item mega-menu-item-type-post_type mega-menu-item-object-page mega-align-bottom-left mega-menu-flyout mega-menu-item-21" id="mega-menu-item-21"><a class="mega-menu-link" href="https://aghawkdynamics.com/about-us/" tabindex="0">About</a></li><li class="mega-menu-item mega-menu-item-type-post_type mega-menu-item-object-page mega-align-bottom-left mega-menu-flyout mega-menu-item-24" id="mega-menu-item-24"><a class="mega-menu-link" href="https://aghawkdynamics.com/contact-us/" tabindex="0">Contact Us</a></li></ul></div>	</nav><!-- #site-navigation -->
			</div><!--//#navBar-->
		            
    </div><!--//.pad-->
		
</header>
	
	

<div id="page" class="site <?php echo $post_slug; ?>">
	<div id="content" class="site-content">
		
		
		
		
		