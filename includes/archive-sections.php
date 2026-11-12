<?php
/**
 * Abschnitte der Archive: Rezeptarchiv und Essensplan-Archiv.
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
 * Gilt nur für die beiden Post-Type-Archive. Die Taxonomie-Archive
 * (Rezeptkategorie, -schlagwort, Plankategorie) laufen weiter über Astras
 * archive.php und behalten ihre Hooks unverändert.
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

	$types['recipe_archive'] = __( 'Recipe archive', 'mixmymeals-hello-child' );
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

	if ( 'recipe_archive' === $type ) {
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
				'label'  => __( 'Diet filter', 'mixmymeals-hello-child' ),
				'render' => 'mmm_archive_section_recipe_filter',
				'hero'   => true,
			),
			'browse'      => array(
				'label'  => __( 'Browse card', 'mixmymeals-hello-child' ),
				'render' => 'mmm_archive_section_browse',
			),
			'heading'     => array(
				'label'  => __( 'List heading', 'mixmymeals-hello-child' ),
				'render' => 'mmm_archive_section_heading',
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

	if ( is_post_type_archive( 'mmm_recipe' ) ) {
		return 'recipe_archive';
	}

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
 * Ernährungs-Pillen über dem Rezeptarchiv.
 *
 * Denselben Blocknamen wie das Planarchiv, aber einen eigenen Callback: die
 * Registry gilt je Seitentyp, und eine gespeicherte Reihenfolge braucht keine
 * Wanderung, weil mmm_single_section_order() einen unbekannten Block an seine
 * Stelle in der Registry schiebt.
 */
function mmm_archive_section_recipe_filter(): void {
	echo do_shortcode( '[mmm_archive_filters]' );
}

/**
 * Stöber-Karte: neue und liegengebliebene Rezepte, dazu die Kategorien.
 */
function mmm_archive_section_browse(): void {

	if ( function_exists( 'mmm_render_recipe_browse_card' ) ) {
		mmm_render_recipe_browse_card();
	}
}

/**
 * Überschrift über der Liste.
 *
 * „Alle Rezepte" wäre über einer gefilterten Liste gelogen, also sagt die
 * Überschrift, was tatsächlich darunter steht, solange eine Suche läuft.
 *
 * Eine Stöber-Ansicht bekommt hier gar keine Überschrift: sie betitelt die
 * Seite selbst (mmm_browse_view_archive_title()) und trägt ihre Beschreibung
 * im Kopf, wie ein Schlagwort-Archiv — eine zweite Überschrift, die die H1
 * wiederholt, ist genau das, was jene Archive nicht haben.
 */
function mmm_archive_section_heading(): void {

	$view = function_exists( 'mmm_archive_view' ) ? mmm_archive_view() : '';

	if ( ! have_posts() || '' !== $view ) {
		return;
	}

	$term = function_exists( 'mmm_archive_search_term' ) ? mmm_archive_search_term() : '';
	?>
	<h2 class="mmm-recipe-archive-heading">
		<?php
		if ( '' !== $term ) {
			printf(
				/* translators: %s: the search term. */
				esc_html__( 'Search results for “%s”', 'mixmymeals-hello-child' ),
				esc_html( $term )
			);
		} else {
			esc_html_e( 'All recipes', 'mixmymeals-hello-child' );
		}
		?>
	</h2>
	<?php
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
