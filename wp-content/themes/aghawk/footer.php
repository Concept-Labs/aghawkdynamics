<?php //AGHAWK
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

?>
			</main><!-- #main -->
		</div><!-- #primary -->



	<div class="ftrRequestDemo" id="request-demo">
		<div class="pad">
			<div class="formWrap">
				<h3>Request a Live Demo</h3>
				<?php echo do_shortcode('[contact-form-7 id="429437e" title="Grand Opening Registration"]'); ?>
			</div>
			<div class="ctaBlurb">
				<p>Experience firsthand how AgHawk Dynamics can revolutionize your processes with precision drone services. Request a live demo today and discover how we can help you boost efficiency, save resources, and increase productivity.</p>
			</div>

			<br class="Clear" />

		</div><!--//.pad-->
	</div><!--//.ftrRequestDemo-->


	<div class="ftrWatermark">

		<div class="servicesTiles">
			<div class="pad">

				<a class="tile spraying" href="<?php echo esc_url( home_url( '/' ) ); ?>services/precision-spraying">
					<h4>Precision<br />Spraying</h4>
					<p></p>
				</a>

				<a class="tile spreading" href="<?php echo esc_url( home_url( '/' ) ); ?>services/precision-spreading">
					<h4>Precision<br />Spreading</h4>
				</a>

				<a class="tile mapping" href="<?php echo esc_url( home_url( '/' ) ); ?>services/fruit-drying">
					<h4>Fruit<br />Drying</h4>
				</a>

				<a class="tile analytics" href="<?php echo esc_url( home_url( '/' ) ); ?>services/crop-analytics">
					<h4>Crop<br />Analytics</h4>
				</a>
                
                <h3>Serving Chelan, Douglas, and Grant Counties</h3>
			</div><!--//.pad-->
		</div><!--//.servicesPanel-->



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
					<a class="nwwebdev" target="_blank" href="https://nwwebdev.com" title="Wordpress website design and development">Website: Northwest Website Development</a>
				</div>
			</div><!--//.pad-->
		</div> <!--//Footer-->

	</div><!--//.ftrWatermark-->

</div><!-- #page .site -->

<?php wp_footer(); ?>

</body>
</html>
