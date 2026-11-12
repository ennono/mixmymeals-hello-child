<?php
/**
 * Navigation: die feste Tab-Leiste am unteren Rand (Handy und Tablet).
 *
 * Der Planer ist das Produkt, lag aber hinter einem Knopf im Header, ganz
 * oben auf Seiten, die mobil bis zu 20 000 px lang sind. Die Leiste legt die
 * drei Schritte des Ablaufs dorthin, wo der Daumen ist: Plan, Rezepte,
 * Einkaufsliste. Sie ersetzt die Bodenleiste „Zum Planer“, die das Plugin nur
 * bei gefülltem Plan einblendete.
 *
 * Die Adressen kommen aus WordPress (Seiten-Template, Archiv-Link) und nicht
 * aus einer Einstellung, damit keine Stelle eine andere Adresse kennt als die
 * Seite selbst.
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adresse der Einkaufslisten-Seite: die erste veröffentlichte Seite mit dem
 * Template page-templates/einkaufsliste.php. Leer, wenn es keine gibt.
 *
 * @return string
 */
function mmm_shopping_list_page_url(): string {

	static $url = null;

	if ( null !== $url ) {
		return $url;
	}

	$pages = get_pages(
		array(
			'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one small query per request.
			'meta_value'  => 'page-templates/einkaufsliste.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'post_status' => 'publish',
			'number'      => 1,
		)
	);

	$url = $pages ? (string) get_permalink( $pages[0] ) : '';

	return $url;
}

/**
 * Die Tabs der Leiste. Ein Tab ohne Adresse (Seite fehlt, Plugin aus) fällt
 * weg, statt ins Leere zu zeigen.
 *
 * @return array<string, array{url:string,label:string,icon:string,current:bool,badge:bool}>
 */
function mmm_tabbar_items(): array {

	$items = array();

	$planner = function_exists( 'mmm_planner_page_url' ) ? mmm_planner_page_url() : '';
	if ( '' !== $planner ) {
		$items['plan'] = array(
			'url'     => $planner,
			'label'   => __( 'Plan', 'mixmymeals-hello-child' ),
			'icon'    => 'fa-calendar-days',
			'current' => is_page_template( 'page-templates/planer.php' ),
			'badge'   => true,
		);
	}

	$recipes = post_type_exists( 'mmm_recipe' ) ? get_post_type_archive_link( 'mmm_recipe' ) : false;
	if ( $recipes ) {
		$items['recipes'] = array(
			'url'     => (string) $recipes,
			'label'   => __( 'Recipes', 'mixmymeals-hello-child' ),
			'icon'    => 'fa-bowl-food',
			'current' => is_post_type_archive( 'mmm_recipe' )
				|| is_singular( 'mmm_recipe' )
				|| is_tax( get_object_taxonomies( 'mmm_recipe' ) ),
			'badge'   => false,
		);
	}

	$shopping = mmm_shopping_list_page_url();
	if ( '' !== $shopping ) {
		$items['shopping'] = array(
			'url'     => $shopping,
			'label'   => __( 'Shopping list', 'mixmymeals-hello-child' ),
			'icon'    => 'fa-cart-shopping',
			'current' => is_page_template( 'page-templates/einkaufsliste.php' ),
			'badge'   => false,
		);
	}

	/**
	 * Tabs der Leiste am unteren Rand.
	 *
	 * @param array $items Schlüssel => url, label, icon, current, badge.
	 */
	return (array) apply_filters( 'mmm_tabbar_items', $items );
}

/**
 * Gibt die Leiste aus. Der Zähler ist das Badge des Plugins
 * ([mmm_cart_badge]): frontend.js füllt jedes .mmm-cart-badge der Seite.
 */
function mmm_render_tabbar(): void {

	$items = mmm_tabbar_items();

	// Eine Leiste mit einem einzigen Tab ist keine Navigation.
	if ( count( $items ) < 2 ) {
		return;
	}

	echo '<nav class="mmm-tabbar" aria-label="' . esc_attr__( 'Main sections', 'mixmymeals-hello-child' ) . '">';

	foreach ( $items as $key => $item ) {
		printf(
			'<a class="mmm-tabbar__tab mmm-tabbar__tab--%1$s" href="%2$s"%3$s>'
			. '<span class="mmm-tabbar__icon"><i class="fa-solid %4$s" aria-hidden="true"></i></span>'
			. '<span class="mmm-tabbar__label">%5$s</span>%6$s'
			. '</a>',
			esc_attr( sanitize_html_class( (string) $key ) ),
			esc_url( $item['url'] ),
			empty( $item['current'] ) ? '' : ' aria-current="page"',
			esc_attr( $item['icon'] ),
			esc_html( $item['label'] ),
			empty( $item['badge'] )
				? ''
				: '<span class="mmm-cart-badge mmm-tabbar__badge" data-label-singular="" data-label-plural=""></span>'
		);
	}

	echo '</nav>';
}
add_action( 'wp_footer', 'mmm_render_tabbar', 5 );

/**
 * Body-Klasse, solange die Leiste ausgegeben wird: das CSS hält damit das
 * Seitenende, die Speicherleiste des Planers und die Einblendungen frei.
 *
 * @param string[] $classes Body-Klassen.
 * @return string[]
 */
function mmm_tabbar_body_class( $classes ) {

	if ( count( mmm_tabbar_items() ) >= 2 ) {
		$classes[] = 'mmm-has-tabbar';
	}

	return $classes;
}
add_filter( 'body_class', 'mmm_tabbar_body_class' );
