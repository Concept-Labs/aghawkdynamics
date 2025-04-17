<?php //AGHAWK 
/**
 * Displays the site header.
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

$wrapper_classes  = 'site-header';
$wrapper_classes .= has_custom_logo() ? ' has-logo' : '';
$wrapper_classes .= ( true === get_theme_mod( 'display_title_and_tagline', true ) ) ? ' has-title-and-tagline' : '';
$wrapper_classes .= has_nav_menu( 'primary' ) ? ' has-menu' : '';
?>

<header id="masthead" class="site-header <?php //echo esc_attr( $wrapper_classes ); ?>" role="banner">

    <div class="pad">
        <h1 id="hdrLogo" class="site-title">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="Agricultural Drone Applications" rel="home">
                <img src="<?php echo get_template_directory_uri(); ?>/images/logo-long.png?v=<?php echo time(); ?>" border="0" />
            </a>
        </h1>
            
		<div id="navBar">
			<?php get_template_part( 'template-parts/header/site-nav' ); ?>
		</div><!--//#navBar-->
		
        					
		<div class="hdrRequestDemo">
			<a href="#request-demo">Request Demo</a>
		</div>
            
    </div><!--//.pad-->
	
	<script type="text/javascript">
		$(document).ready(function() {
			$('.hdrRequestDemo a').click(function(e) {
				e.preventDefault(); 
				$('html, body').animate({
					scrollTop: $('#request-demo').offset().top - 120
				}, 500, 'swing'); // Built-in easing effect
			});
		});
	</script>
	
</header><!-- #masthead -->



