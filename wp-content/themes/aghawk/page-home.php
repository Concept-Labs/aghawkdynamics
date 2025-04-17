<?php // AGHAWK
/**
 * Template Name: Home Page
 */


get_header();


?>


<div class="homeVideoContainer <?php echo $post_slug; ?>">
	<video autoplay="" loop="" muted="" playsinline="" poster="<?php echo get_template_directory_uri() . '/images/video1.jpg'; ?>">	
		<source src="<?php echo get_template_directory_uri() . '/images/video1.mp4'; ?>" type="video/mp4">	
	</video>
	<div class="homeVideoCTAOverlay">
		<p class="tagline">Smart decision with rapid precision.</p>
	</div>
</div><!--//.homeVideoContainer-->



<div class="homeSummary">
	<div class="pad">
		<?php
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/content-page' );

				// If comments are open or there is at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
			endwhile; // End of the loop.
		?>
	</div><!--//.pad-->
</div><!--//.homeSummary-->




<?php
	get_footer();

