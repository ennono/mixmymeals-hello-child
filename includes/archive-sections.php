<?php
/**
 * Abschnitte des Essensplan-Archivs.
 *
 * Das Rezeptarchiv ist hier heraus: seine Gestaltung liegt seit dem Umstieg
 * in einer Archiv-Vorlage des Elementor Theme Builders. Das Essensplan- und
 * das Zutaten-Archiv laufen weiter über diese Mechanik.
 *
 * Gleiche Mechanik wie bei den Einzelseiten (includes/single-sections.php) —
 * benannte Blöcke, Reihenfolge und Häkchen in derselben Option, dieselbe
 * Sortierliste unter Design → Theme-Einstellungen.
 *
 * Ein Unterschied bleibt: Beschreibung, Suchfeld und Kategorie-Filter stehen
 * nicht im Template, sondern hängen an Astra-Hooks und werden damit INNERHALB
 * des Archiv-Kopfes ausgegeben — der ist eine gestaltete Fläche mit grünem
 * Schimmer, und das Suchfeld ist eigens dafür gebaut (siehe style.css,
 * „Archiv-Suche + Filter"). Diese Blöcke bleiben deshalb im Kopf, solange sie
 * in der Reihenfolge vor allen anderen stehen. Zieht man einen von ihnen hinter
 * einen anderen Block, verlässt er den Kopf und erscheint an der neuen Stelle
 * im Seitenfluss. Das ist die einzige Lesart, bei der die Standardreihenfolge
 * exakt die bisherige Seite ergibt und Umsortieren trotzdem tut, was man
 * erwartet.
 *
 * Die Taxonomie-Archive (Rezeptkategorie, -schlagwort, Plankategorie) laufen
 * weiter über die archive.php dieses Themes und behalten ihre Hooks
 * unverändert — auch die des Rezeptarchivs (Suchfeld, Chips, Bewertung,
 * Planen-Knopf in includes/recipe_archive.php). Sie sind also NICHT tot,
 * solange die Kategorie- und Schlagwortseiten der Rezepte hier gerendert
 * werden; erst wenn auch die nach Elementor gehen, können sie weg.
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
==========================================================
	Registry
	========================================================== */

add_filter( 'mmm_single_section_types', 'mmm_archive_section_types' );

/**
 * Die Archive als sortierbare Typen anmelden.
 *
 * @param array<string, string> $types Bisherige Typen.
 * @return array<string, string>
 */
function mmm_archive_section_types( array $types ): array {

	$types['plan_archive']   = __( 'Meal plan archive', 'mixmymeals-hello-child' );

	return $types;
}

add_filter( 'mmm_single_section_registry', 'mmm_archive_section_registry', 10, 2 );

/**
 * Die Blöcke der beiden Archive.
 *
 * hero = true: der Block wird über einen Astra-Hook im Archiv-Kopf ausgegeben,
 * solange er in der Reihenfolge vor allen anderen steht.
 *
 * @param array  $sections Bisherige Registry (leer für diese Typen).
 * @param string $type     Seitentyp.
 * @return array<string, array{label: string, render: callable, hero?: bool}>
 */
function mmm_archive_section_registry( array $sections, string $type ): array {

	if ( 'plan_archive' === $type ) {
		return array(
			'description' => array(
				'label'  => __( 'Archive description', 'mixmymeals-hello-child' ),
				'render' => 'mmm_archive_section_description',
				'hero'   => true,
			),
			'search'      => array(
				'label'  => __( 'Search form', 'mixmymeals-hello-child' ),
				'render' => 'mmm_archive_section_search',
				'hero'   => true,
			),
			'filter'      => array(
				'label'  => __( 'Category filter', 'mixmymeals-hello-child' ),
				'render' => 'mmm_archive_section_filter',
				'hero'   => true,
			),
			'list'        => array(
				'label'  => __( 'The list', 'mixmymeals-hello-child' ),
				'render' => 'mmm_archive_section_list',
			),
			'pagination'  => array(
				'label'  => __( 'Pagination', 'mixmymeals-hello-child' ),
				'render' => 'mmm_archive_section_pagination',
			),
		);
	}

	return $sections;
}

/*
==========================================================
	Kopf und Seitenfluss
	========================================================== */

/**
 * Hat dieser Typ Blöcke, die in den Archiv-Kopf gehören? Die Einstellungsseite
 * blendet danach ihren Hinweis ein.
 */
function mmm_archive_section_has_hero( string $type ): bool {

	foreach ( mmm_single_section_registry( $type ) as $section ) {
		if ( ! empty( $section['hero'] ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Der Abschnittstyp des gerade angezeigten Archivs, '' außerhalb der beiden.
 */
function mmm_archive_section_type(): string {

	if ( is_post_type_archive( 'mmm_meal_plan' ) ) {
		return 'plan_archive';
	}

	return '';
}

/**
 * Welche Blöcke in den Kopf gehören und welche in den Seitenfluss.
 *
 * Sichtbare Blöcke in gespeicherter Reihenfolge; die Kopf-Blöcke ganz vorne
 * bleiben im Kopf, ab dem ersten Block, der nicht dorthin gehört, läuft alles
 * im Seitenfluss weiter.
 *
 * @return array{hero: string[], flow: string[]}
 */
function mmm_archive_section_slots( string $type ): array {

	$registry = mmm_single_section_registry( $type );
	$hero     = array();
	$flow     = array();
	$in_head  = true;

	foreach ( mmm_single_section_order( $type ) as $slug ) {

		if ( ! mmm_single_section_is_visible( $type, $slug ) ) {
			continue;
		}

		if ( $in_head && ! empty( $registry[ $slug ]['hero'] ) ) {
			$hero[] = $slug;
			continue;
		}

		$in_head = false;
		$flow[]  = $slug;
	}

	return array(
		'hero' => $hero,
		'flow' => $flow,
	);
}

/**
 * Einen Satz Blöcke ausgeben.
 *
 * @param string   $type  Seitentyp.
 * @param string[] $slugs Blöcke in der Reihenfolge, in der sie erscheinen.
 */
function mmm_render_archive_section_list( string $type, array $slugs ): void {

	$registry = mmm_single_section_registry( $type );

	foreach ( $slugs as $slug ) {

		$render = $registry[ $slug ]['render'] ?? null;

		if ( is_callable( $render ) ) {
			call_user_func( $render );
		}
	}
}

/**
 * Die Blöcke im Seitenfluss. Das ist alles, was in den Archiv-Templates steht.
 */
function mmm_render_archive_sections( string $type ): void {

	$slots = mmm_archive_section_slots( $type );

	mmm_render_archive_section_list( $type, $slots['flow'] );
}

add_action( 'wp', 'mmm_archive_sections_take_over' );

/**
 * Auf den beiden Post-Type-Archiven übernimmt die Reihenfolge den Kopf.
 *
 * Die drei Blöcke hängen sonst einzeln an Astra-Hooks; hier werden sie
 * abgehängt und stattdessen in gespeicherter Reihenfolge an derselben Stelle
 * ausgegeben. Auf allen anderen Archiven bleibt alles, wie es war.
 */
function mmm_archive_sections_take_over(): void {

	if ( '' === mmm_archive_section_type() ) {
		return;
	}

	remove_action( 'mmm_after_archive_title', 'mmm_print_archive_description_from_option' );
	remove_action( 'mmm_after_archive_title', 'mmm_add_archive_search_to_archive', 30 );
	remove_action( 'mmm_after_archive_description', 'mmm_print_meal_plan_category_filter' );

	add_action( 'mmm_after_archive_title', 'mmm_render_archive_hero_sections', 10 );
}

/**
 * Die Kopf-Blöcke, an derselben Stelle wie bisher.
 */
function mmm_render_archive_hero_sections(): void {

	$type = mmm_archive_section_type();

	if ( '' === $type ) {
		return;
	}

	$slots = mmm_archive_section_slots( $type );

	mmm_render_archive_section_list( $type, $slots['hero'] );
}

/*
==========================================================
	Die Blöcke
	========================================================== */

/**
 * Archivbeschreibung aus den Optionen (bei einer Stöber-Ansicht deren eigener
 * Hinweis).
 */
function mmm_archive_section_description(): void {

	// Auf der ungefilterten Rezeptübersicht steht die Einleitung unter den
	// Ergebnissen (includes/redesign.php), nicht im Kopf.
	if ( function_exists( 'mmm_is_plain_recipe_archive' ) && mmm_is_plain_recipe_archive() ) {
		return;
	}

	mmm_print_archive_description_from_option();
}

/**
 * Suchfeld samt Filtern.
 */
function mmm_archive_section_search(): void {
	mmm_add_archive_search_to_archive();
}

/**
 * Kategorie-Umschalter des Planarchivs.
 */
function mmm_archive_section_filter(): void {
	mmm_print_meal_plan_category_filter();
}




/**
 * Die Liste selbst.
 *
 * Immer Astras Schleife, ob leer oder nicht: sie öffnet das div.ast-row, das
 * Inhaltsbreite und Abstände trägt. Der Leer-Zustand wird darin ausgetauscht,
 * von template-parts/content-none.php.
 */
function mmm_archive_section_list(): void {
	mmm_content_loop();
}

/**
 * Blätterlinks.
 */
function mmm_archive_section_pagination(): void {
	mmm_pagination();
}
