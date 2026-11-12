<?php
/**
 * Kopf jeder Seite.
 *
 * Der sichtbare Header kommt aus dem Theme Builder von Elementor Pro
 * (Position „header“). Gibt es dort keinen, greift der schlichte Header von
 * Hello Elementor. Darunter folgen die Brotkrumen und der Container, in dem
 * die Templates dieses Themes ihr #primary ausgeben; footer.php schließt ihn.
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body itemtype="<?php echo esc_url( mmm_layout_body_itemtype() ); ?>" itemscope="itemscope" <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'mixmymeals-hello-child' ); ?></a>
<div id="page" class="hfeed site">
<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
	if ( function_exists( 'hello_elementor_display_header_footer' ) && hello_elementor_display_header_footer() ) {
		if ( did_action( 'elementor/loaded' ) && function_exists( 'hello_header_footer_experiment_active' ) && hello_header_footer_experiment_active() ) {
			get_template_part( 'template-parts/dynamic-header' );
		} else {
			get_template_part( 'template-parts/header' );
		}
	}
}

mmm_breadcrumbs();
?>
	<div id="content" class="site-content">
		<div class="mmm-l-container">
