<?php
/**
 * Fuß jeder Seite: schließt den Container aus header.php. Der sichtbare
 * Footer kommt aus dem Theme Builder von Elementor Pro (Position „footer“),
 * sonst der schlichte von Hello Elementor.
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
		</div><!-- .mmm-l-container -->
	</div><!-- #content -->
<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
	if ( function_exists( 'hello_elementor_display_header_footer' ) && hello_elementor_display_header_footer() ) {
		if ( did_action( 'elementor/loaded' ) && function_exists( 'hello_header_footer_experiment_active' ) && hello_header_footer_experiment_active() ) {
			get_template_part( 'template-parts/dynamic-footer' );
		} else {
			get_template_part( 'template-parts/footer' );
		}
	}
}
?>
</div><!-- #page -->
<?php wp_footer(); ?>
</body>
</html>
