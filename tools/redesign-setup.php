<?php
/**
 * Richtet die Navigation des Redesigns in der Datenbank ein.
 *
 *     wp eval-file wp-content/themes/mixmymeals-astra-child/tools/redesign-setup.php
 *     wp eval-file wp-content/themes/mixmymeals-astra-child/tools/redesign-setup.php rollback
 *
 * Menüs, Seiten und der Aufbau des Astra-Headers liegen in der Datenbank und
 * reisen nicht mit dem Theme. Dieses Skript stellt sie her, damit derselbe
 * Stand auf jeder Kopie der Seite entsteht, statt von Hand nachgeklickt zu
 * werden. Es lässt sich beliebig oft ausführen.
 *
 * Was es tut:
 *  - legt die Seite „Einkaufsliste“ an (Template einkaufsliste.php, noindex),
 *  - baut drei eigene Menüs (Haupt, Mobil, Footer) und weist sie den
 *    Menüpositionen zu; die bisherigen Menüs bleiben unverändert bestehen,
 *  - ordnet den Astra-Header: Desktop Logo links, Menü und Suche rechts;
 *    mobil Logo, Suche, Menü-Knopf. Der Knopf „Jetzt planen“ (HTML 1)
 *    entfällt, „Planer“ ist der erste Menüpunkt und trägt den Zähler.
 *
 * Beim ersten Lauf merkt es sich die bisherigen Zuweisungen und den bisherigen
 * Header-Aufbau; „rollback“ stellt genau diese wieder her. Seite und Menüs
 * bleiben dabei bestehen, sie stören nicht.
 *
 * @package MixMyMeals Astra Child
 */

// Ein Werkzeug, keine Seite: eine mit dem Theme hochgeladene Kopie darf
// nicht laufen, wenn jemand ihre Adresse aufruft.
if ( 'cli' !== PHP_SAPI || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

$mmm_mode       = isset( $args[0] ) ? (string) $args[0] : 'apply';
$mmm_backup_key = 'mmm_redesign_nav_backup';
$mmm_astra_key  = defined( 'ASTRA_THEME_SETTINGS' ) ? ASTRA_THEME_SETTINGS : 'astra-settings';

/* ----------------------------------------------------------
 * Zurück auf den Stand vor dem ersten Lauf
 * ---------------------------------------------------------- */
if ( 'rollback' === $mmm_mode ) {

	$mmm_backup = get_option( $mmm_backup_key );

	if ( ! is_array( $mmm_backup ) ) {
		WP_CLI::error( 'Kein gemerkter Stand vorhanden, nichts zurückzusetzen.' );
	}

	set_theme_mod( 'nav_menu_locations', (array) $mmm_backup['nav_menu_locations'] );

	$mmm_astra = (array) get_option( $mmm_astra_key, array() );
	foreach ( array( 'header-desktop-items', 'header-mobile-items' ) as $mmm_key ) {
		if ( isset( $mmm_backup[ $mmm_key ] ) ) {
			$mmm_astra[ $mmm_key ] = $mmm_backup[ $mmm_key ];
		}
	}
	update_option( $mmm_astra_key, $mmm_astra );
	delete_option( $mmm_backup_key );

	WP_CLI::success( 'Menüzuweisungen und Header-Aufbau zurückgesetzt. Seite und Redesign-Menüs bestehen weiter.' );
	return;
}

if ( 'apply' !== $mmm_mode ) {
	WP_CLI::error( 'Unbekannter Modus: ' . $mmm_mode . ' (erlaubt: kein Argument oder „rollback“).' );
}

// Menütitel tragen Symbole und den Zähler als HTML; ohne angemeldeten
// Benutzer würde kses die Klassen daraus entfernen.
kses_remove_filters();

/* ----------------------------------------------------------
 * Bisherigen Stand merken (nur beim ersten Lauf)
 * ---------------------------------------------------------- */
$mmm_astra = (array) get_option( $mmm_astra_key, array() );

if ( ! is_array( get_option( $mmm_backup_key ) ) ) {
	add_option(
		$mmm_backup_key,
		array(
			'nav_menu_locations'   => (array) get_theme_mod( 'nav_menu_locations', array() ),
			'header-desktop-items' => $mmm_astra['header-desktop-items'] ?? null,
			'header-mobile-items'  => $mmm_astra['header-mobile-items'] ?? null,
		),
		'',
		false
	);
	WP_CLI::log( 'Bisherigen Stand gemerkt (' . $mmm_backup_key . ').' );
}

$mmm_backup = (array) get_option( $mmm_backup_key );

/* ----------------------------------------------------------
 * Helfer
 * ---------------------------------------------------------- */

/**
 * Erste Seite mit einem bestimmten Seiten-Template, sonst 0.
 */
$mmm_page_by_template = static function ( string $template ): int {
	$pages = get_pages(
		array(
			'meta_key'    => '_wp_page_template',
			'meta_value'  => $template,
			'post_status' => array( 'publish', 'draft', 'private' ),
			'number'      => 1,
		)
	);
	return $pages ? (int) $pages[0]->ID : 0;
};

/**
 * Menüpunkt für eine Seite. Ohne Titel gilt der Seitentitel.
 */
$mmm_page_item = static function ( int $page_id, string $title = '', string $classes = '' ): ?array {
	if ( $page_id <= 0 || 'page' !== get_post_type( $page_id ) ) {
		return null;
	}
	return array(
		'menu-item-type'      => 'post_type',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $page_id,
		'menu-item-title'     => $title,
		'menu-item-classes'   => $classes,
	);
};

/**
 * Menüpunkt für das Archiv eines Inhaltstyps.
 */
$mmm_archive_item = static function ( string $post_type, string $title ): ?array {
	if ( ! post_type_exists( $post_type ) || ! get_post_type_archive_link( $post_type ) ) {
		return null;
	}
	return array(
		'menu-item-type'   => 'post_type_archive',
		'menu-item-object' => $post_type,
		'menu-item-title'  => $title,
	);
};

/**
 * Titel mit Symbol davor.
 */
$mmm_title = static function ( string $icon, string $text ): string {
	return '<i class="fa-solid ' . $icon . '" aria-hidden="true"></i> ' . $text;
};

/**
 * Legt ein Menü an oder leert es und füllt es mit genau diesen Punkten.
 */
$mmm_build_menu = static function ( string $name, array $items ): int {

	$menu    = wp_get_nav_menu_object( $name );
	$menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu( $name );

	if ( is_wp_error( $menu_id ) ) {
		WP_CLI::error( 'Menü „' . $name . '“: ' . $menu_id->get_error_message() );
	}

	foreach ( (array) wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) as $old ) {
		wp_delete_post( $old->ID, true );
	}

	$position = 0;
	foreach ( array_filter( $items ) as $item ) {
		$item['menu-item-position'] = ++$position;
		$item['menu-item-status']   = 'publish';
		$result                     = wp_update_nav_menu_item( (int) $menu_id, 0, $item );
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( 'Menü „' . $name . '“: ' . $result->get_error_message() );
		}
	}

	WP_CLI::log( 'Menü „' . $name . '“: ' . $position . ' Punkte.' );

	return (int) $menu_id;
};

/* ----------------------------------------------------------
 * Seite „Einkaufsliste“
 * ---------------------------------------------------------- */
$mmm_shopping_id = $mmm_page_by_template( 'page-templates/einkaufsliste.php' );

if ( 0 === $mmm_shopping_id ) {

	$mmm_existing = get_page_by_path( 'einkaufsliste' );

	if ( $mmm_existing ) {
		$mmm_shopping_id = (int) $mmm_existing->ID;
	} else {
		$mmm_admins      = get_users(
			array(
				'role'    => 'administrator',
				'number'  => 1,
				'orderby' => 'ID',
				'fields'  => 'ID',
			)
		);
		$mmm_shopping_id = wp_insert_post(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'post_title'     => 'Einkaufsliste',
				'post_name'      => 'einkaufsliste',
				'post_content'   => '',
				'post_author'    => $mmm_admins ? (int) $mmm_admins[0] : 0,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			true
		);
		if ( is_wp_error( $mmm_shopping_id ) ) {
			WP_CLI::error( 'Seite „Einkaufsliste“: ' . $mmm_shopping_id->get_error_message() );
		}
		WP_CLI::log( 'Seite „Einkaufsliste“ angelegt (ID ' . $mmm_shopping_id . ').' );
	}

	update_post_meta( $mmm_shopping_id, '_wp_page_template', 'page-templates/einkaufsliste.php' );
}

// Persönlicher Inhalt: nicht in den Index und nicht in die Sitemap (Yoast).
update_post_meta( $mmm_shopping_id, '_yoast_wpseo_meta-robots-noindex', '1' );

/* ----------------------------------------------------------
 * Menüs
 * ---------------------------------------------------------- */
$mmm_planner_id = $mmm_page_by_template( 'page-templates/planer.php' );
$mmm_blog_id    = (int) get_option( 'page_for_posts' );

if ( 0 === $mmm_planner_id ) {
	WP_CLI::warning( 'Keine Seite mit dem Planer-Template gefunden; der Menüpunkt „Planer“ fehlt.' );
}

// Die rechtlichen Seiten übernimmt das neue Footer-Menü aus dem bisherigen.
$mmm_legal        = array();
$mmm_old_footer   = (int) ( $mmm_backup['nav_menu_locations']['footer_menu'] ?? 0 );
$mmm_old_footer_i = $mmm_old_footer ? wp_get_nav_menu_items( $mmm_old_footer ) : array();

foreach ( (array) $mmm_old_footer_i as $mmm_old ) {
	$mmm_legal[] = array(
		'menu-item-type'      => $mmm_old->type,
		'menu-item-object'    => $mmm_old->object,
		'menu-item-object-id' => (int) $mmm_old->object_id,
		'menu-item-url'       => 'custom' === $mmm_old->type ? $mmm_old->url : '',
		'menu-item-title'     => 'custom' === $mmm_old->type ? $mmm_old->title : $mmm_old->post_title,
	);
}

$mmm_main_menu = $mmm_build_menu(
	'Hauptmenü (Redesign)',
	array(
		$mmm_page_item(
			$mmm_planner_id,
			$mmm_title( 'fa-calendar-days', 'Planer' )
				. ' <span class="mmm-cart-badge" data-label-singular="" data-label-plural=""></span>',
			'mmm-menu-planner'
		),
		$mmm_archive_item( 'mmm_recipe', $mmm_title( 'fa-bowl-food', 'Rezepte' ) ),
		$mmm_archive_item( 'mmm_meal_plan', $mmm_title( 'fa-clipboard-list', 'Fertige Pläne' ) ),
		$mmm_page_item( $mmm_shopping_id, $mmm_title( 'fa-cart-shopping', 'Einkaufsliste' ) ),
	)
);

$mmm_mobile_menu = $mmm_build_menu(
	'Mobilmenü (Redesign)',
	array_merge(
		array(
			$mmm_archive_item( 'mmm_meal_plan', $mmm_title( 'fa-clipboard-list', 'Fertige Pläne' ) ),
			$mmm_archive_item( 'mmm_ingredient', $mmm_title( 'fa-carrot', 'Zutaten' ) ),
			$mmm_page_item( $mmm_blog_id, $mmm_title( 'fa-book', 'Blog' ) ),
		),
		$mmm_legal
	)
);

$mmm_footer_menu = $mmm_build_menu(
	'Footer (Redesign)',
	array_merge(
		array(
			$mmm_archive_item( 'mmm_ingredient', 'Zutaten' ),
			$mmm_page_item( $mmm_blog_id, 'Blog' ),
		),
		$mmm_legal
	)
);

$mmm_locations                = (array) get_theme_mod( 'nav_menu_locations', array() );
$mmm_locations['primary']     = $mmm_main_menu;
$mmm_locations['mobile_menu'] = $mmm_mobile_menu;
$mmm_locations['footer_menu'] = $mmm_footer_menu;
set_theme_mod( 'nav_menu_locations', $mmm_locations );

/* ----------------------------------------------------------
 * Aufbau des Astra-Headers
 * ---------------------------------------------------------- */
$mmm_astra = (array) get_option( $mmm_astra_key, array() );

if ( isset( $mmm_astra['header-desktop-items']['primary'] ) ) {
	$mmm_astra['header-desktop-items']['primary']['primary_center'] = array();
	$mmm_astra['header-desktop-items']['primary']['primary_right']  = array( 'menu-1', 'search' );
}

if ( isset( $mmm_astra['header-mobile-items']['primary'] ) ) {
	$mmm_astra['header-mobile-items']['primary']['primary_center'] = array();
	$mmm_astra['header-mobile-items']['primary']['primary_right']  = array( 'search', 'mobile-trigger' );
	$mmm_astra['header-mobile-items']['popup']['popup_content']    = array( 'mobile-menu' );
}

update_option( $mmm_astra_key, $mmm_astra );

WP_CLI::success( 'Navigation des Redesigns eingerichtet. Einkaufsliste: ' . get_permalink( $mmm_shopping_id ) );
