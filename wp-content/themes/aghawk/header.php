<?php //AGHAWK ADMIN 

?>
<!doctype html>
<html <?php language_attributes(); ?> <?php twentytwentyone_the_html_classes(); ?>>
<head>
	
	
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-44BZ3KHVVG"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', 'G-44BZ3KHVVG');
	</script>
	
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-NG48ZX73KD"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'G-NG48ZX73KD');
	</script>
	
	<!-- Google tag (gtag.js) , from SARAH -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-NHDL7HJ1EK"></script>
	<script>
		window.dataLayer = window.dataLayer || []; 
		function gtag(){dataLayer.push(arguments);} 
		gtag('js', new Date());
		gtag('config', 'G-NHDL7HJ1EK');
	</script>

	
	
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
	

	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&family=Recursive:wght@300..1000&display=swap" rel="stylesheet">

	
	
    <link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/favicon.png?t=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles_custom.css?t=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles_navigation.css?t=<?php echo time(); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles_home.css?t=<?php echo time(); ?>" />	
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/styles_responsive.css?t=<?php echo time(); ?>" />
    
</head>

<?php
	//get page ID
	global $post;
	$pageID = $post->ID;
	$post_slug = $post->post_name;
?>
	<!--
		pageID = <?php echo $pageID; ?>
		post_slug = <?php echo $post_slug; ?>
	-->	

<body class="<?php echo $post_slug; ?>" <?php //body_class(); ?>>
	
<?php wp_body_open(); ?>
		
	<?php get_template_part( 'template-parts/header/site-header' ); ?>


	
	<script type="text/javascript">
	$(document).ready(function() {
		<!--
		//sticky header
		$(window).scroll(function() {		
			if ($(this).scrollTop() > 200) { // Check if the scroll position is more than 200px
				$('.site-header').addClass('sticky'); // Add 'sticky' class to the 'site-header'
			} else {			
				$('.site-header').removeClass('sticky'); // Remove 'sticky' class from the 'site-header'
			}
		});
		<!--//-->
	});
	</script>

	

<div id="page" class="site <?php echo $post_slug; ?>">

	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'twentytwentyone' ); ?></a>

	<div id="content" class="site-content">
    <!--
		<div id="primary" class="content-area">        
			<main id="main" class="site-main" role="main">
    <!--//-->
            