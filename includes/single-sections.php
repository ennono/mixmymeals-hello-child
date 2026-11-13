<?php
/**
 * Abschnitte der Einzelseiten: Registry, Reihenfolge, Sichtbarkeit.
 *
 * Rezept- und Mahlzeitenseite bestanden aus einer festen Folge von
 * <section>-Blöcken im Template. Umsortieren hieß: Template bearbeiten. Hier
 * bekommt jeder Block einen Namen und eine Renderfunktion; die Templates geben
 * nur noch mmm_render_single_sections() aus, die Reihenfolge steht in einer
 * Option und ist unter Design → Theme-Einstellungen per Drag & Drop änderbar.
 *
 * Die Blöcke bleiben dabei genau die, die sie waren — inklusive der Regel,
 * dass ein Abschnitt ohne Inhalt gar nichts ausgibt. Ein ausgeblendeter
 * Abschnitt ist etwas anderes: er hätte Inhalt, soll aber nicht erscheinen.
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const MMM_CHILD_SECTIONS_OPTION = 'mmm_single_sections';

/*
==========================================================
	Registry
	========================================================== */

/**
 * Alle Abschnitte einer Einzelseite in ihrer Standardreihenfolge.
 *
 * label  erscheint auf der Einstellungsseite, sonst nirgends.
 * render gibt den Abschnitt aus (echo), inklusive <section> und Überschrift.
 *
 * @param string $type 'recipe' oder 'meal'.
 * @return array<string, array{label: string, render: callable}>
 */
function mmm_single_section_registry( string $type ): array {

	$sections = array();

	if ( 'recipe' === $type ) {
		$sections = mmm_recipe_sections();
	} elseif ( 'meal' === $type ) {
		$sections = mmm_meal_sections();
	} elseif ( 'plan' === $type ) {
		$sections = mmm_plan_sections();
	}

	return (array) apply_filters( 'mmm_single_section_registry', $sections, $type );
}

/**
 * Die Seitentypen, die sich sortieren lassen, mit ihrer Beschriftung.
 *
 * Filterbar, damit die Archive ihre Typen in ihrer eigenen Datei anmelden
 * können (includes/archive-sections.php) statt hier mitgeführt zu werden.
 *
 * @return array<string, string>
 */
function mmm_single_section_types(): array {
	return (array) apply_filters(
		'mmm_single_section_types',
		array(
			'recipe' => __( 'Recipe page', 'mixmymeals-hello-child' ),
			'meal'   => __( 'Meal page', 'mixmymeals-hello-child' ),
			'plan'   => __( 'Meal plan page', 'mixmymeals-hello-child' ),
		)
	);
}

/**
 * Abschnitte der Rezeptseite.
 *
 * @return array<string, array{label: string, render: callable}>
 */
function mmm_recipe_sections(): array {
	return array(
		'feature_image'   => array(
			'label'  => __( 'Feature image', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_feature_image',
		),
		'ingredients'     => array(
			'label'  => __( 'Ingredients', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_ingredients',
		),
		'equipment'       => array(
			'label'  => __( 'Required Equipment', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_equipment',
		),
		'steps'           => array(
			'label'  => __( 'Cooking Instructions', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_steps',
		),
		'author'          => array(
			'label'  => __( 'Author box', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_author',
		),
		'nutrition'       => array(
			'label'  => __( 'Nutrition Details', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_nutrition',
		),
		'product_impact'  => array(
			'label'  => __( 'Product choice', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_product_impact',
		),
		'content'         => array(
			'label'  => __( 'Editor content', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_content',
		),
		'related'         => array(
			'label'  => __( 'Goes well with', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_related',
		),
		'related_reverse' => array(
			'label'  => __( 'Fits with', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_related_reverse',
		),
		'plans'           => array(
			'label'  => __( 'Included in these plans', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_plans',
		),
		'note'            => array(
			'label'  => __( 'Note', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_note',
		),
		'photos'          => array(
			'label'  => __( 'Cooked by You', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_photos',
		),
		'ratings'         => array(
			'label'  => __( 'Ratings', 'mixmymeals-hello-child' ),
			'render' => 'mmm_recipe_section_ratings',
		),
	);
}

/**
 * Abschnitte der Mahlzeitenseite.
 *
 * @return array<string, array{label: string, render: callable}>
 */
function mmm_meal_sections(): array {
	return array(
		'feature_image'  => array(
			'label'  => __( 'Feature image', 'mixmymeals-hello-child' ),
			'render' => 'mmm_meal_section_feature_image',
		),
		'ingredients'    => array(
			'label'  => __( 'Ingredients', 'mixmymeals-hello-child' ),
			'render' => 'mmm_meal_section_ingredients',
		),
		'equipment'      => array(
			'label'  => __( 'Required Equipment', 'mixmymeals-hello-child' ),
			'render' => 'mmm_meal_section_equipment',
		),
		'steps'          => array(
			'label'  => __( 'Cooking Instructions', 'mixmymeals-hello-child' ),
			'render' => 'mmm_meal_section_steps',
		),
		'author'         => array(
			'label'  => __( 'Author box', 'mixmymeals-hello-child' ),
			'render' => 'mmm_meal_section_author',
		),
		'nutrition'      => array(
			'label'  => __( 'Nutrition Details', 'mixmymeals-hello-child' ),
			'render' => 'mmm_meal_section_nutrition',
		),
		'product_impact' => array(
			'label'  => __( 'Product choice', 'mixmymeals-hello-child' ),
			'render' => 'mmm_meal_section_product_impact',
		),
		'calculator'     => array(
			'label'  => __( 'How much did you eat?', 'mixmymeals-hello-child' ),
			'render' => 'mmm_meal_section_calculator',
		),
		'note'           => array(
			'label'  => __( 'Note', 'mixmymeals-hello-child' ),
			'render' => 'mmm_meal_section_note',
		),
		'content'        => array(
			'label'  => __( 'Editor content', 'mixmymeals-hello-child' ),
			'render' => 'mmm_meal_section_content',
		),
	);
}

/**
 * Abschnitte der Planseite.
 *
 * Autorenkasten und Beitragsbild standen bisher über der .entry-content und
 * ließen sich damit nicht mit einsortieren. Sie liegen jetzt darin, wie auf
 * Rezept- und Mahlzeitenseite auch.
 *
 * @return array<string, array{label: string, render: callable}>
 */
function mmm_plan_sections(): array {
	return array(
		'author'         => array(
			'label'  => __( 'Author box', 'mixmymeals-hello-child' ),
			'render' => 'mmm_plan_section_author',
		),
		'feature_image'  => array(
			'label'  => __( 'Feature image', 'mixmymeals-hello-child' ),
			'render' => 'mmm_plan_section_feature_image',
		),
		'meals'          => array(
			'label'  => __( 'Meals in this plan', 'mixmymeals-hello-child' ),
			'render' => 'mmm_plan_section_meals',
		),
		'nutrition'      => array(
			'label'  => __( 'Nutrition Overview', 'mixmymeals-hello-child' ),
			'render' => 'mmm_plan_section_nutrition',
		),
		'shopping_list'  => array(
			'label'  => __( 'Shopping List', 'mixmymeals-hello-child' ),
			'render' => 'mmm_plan_section_shopping_list',
		),
		'product_impact' => array(
			'label'  => __( 'Product choice', 'mixmymeals-hello-child' ),
			'render' => 'mmm_plan_section_product_impact',
		),
		'equipment'      => array(
			'label'  => __( 'Required Equipment', 'mixmymeals-hello-child' ),
			'render' => 'mmm_plan_section_equipment',
		),
		'subscribe'      => array(
			'label'  => __( 'Get this plan by e-mail', 'mixmymeals-hello-child' ),
			'render' => 'mmm_plan_section_subscribe',
		),
		'content'        => array(
			'label'  => __( 'Editor content', 'mixmymeals-hello-child' ),
			'render' => 'mmm_plan_section_content',
		),
	);
}

/*
==========================================================
	Reihenfolge und Sichtbarkeit
	========================================================== */

/**
 * Gespeicherte Einstellungen, roh.
 *
 * @return array<string, array{order: string[], hidden: string[]}>
 */
function mmm_single_sections_settings(): array {

	$saved = get_option( MMM_CHILD_SECTIONS_OPTION, array() );

	return is_array( $saved ) ? $saved : array();
}

/**
 * Reihenfolge der Abschnitte eines Seitentyps.
 *
 * Maßgeblich ist die Registry: was dort nicht (mehr) steht, fällt weg. Ein
 * Abschnitt, der im Code dazukommt, rutscht an die Stelle, an der er laut
 * Registry hingehört — das macht mmm_child_merge_order()
 * (includes/theme-settings.php), die auch die Buttonreihe im Plan-Hero
 * sortiert.
 *
 * @param string $type 'recipe' oder 'meal'.
 * @return string[]
 */
function mmm_single_section_order( string $type ): array {

	$settings = mmm_single_sections_settings();

	$order = mmm_child_merge_order(
		(array) ( $settings[ $type ]['order'] ?? array() ),
		array_keys( mmm_single_section_registry( $type ) )
	);

	return (array) apply_filters( 'mmm_single_section_order', $order, $type );
}

/**
 * Ist der Abschnitt eingeschaltet? Ausgeblendet wird als Liste gespeichert,
 * nicht als „sichtbar"-Liste: ein neuer Abschnitt ist damit sichtbar, ohne
 * dass jemand ihn erst freischalten muss.
 */
function mmm_single_section_is_visible( string $type, string $slug ): bool {

	$settings = mmm_single_sections_settings();
	$hidden   = (array) ( $settings[ $type ]['hidden'] ?? array() );

	return (bool) apply_filters(
		'mmm_single_section_is_visible',
		! in_array( $slug, $hidden, true ),
		$slug,
		$type
	);
}

/**
 * Passwortgeschützte Einzelseite: das Passwortformular statt der Seite.
 *
 * WordPress schützt nur the_content(). Diese Templates geben Hero und
 * Abschnitte aber über Shortcodes aus, an the_content() vorbei — ein
 * geschützter Plan war damit für jeden vollständig lesbar. Jedes
 * Single-Template fragt deshalb hier zuerst und überspringt den Rest, wenn
 * true zurückkommt.
 *
 * Die Entscheidung trifft das Plugin (Mixmymeals_Post_Types::is_locked():
 * eine Mahlzeit ist durch das Passwort ihres Plans geschützt); neben einem
 * älteren Plugin gilt WordPress' eigene Prüfung für den Beitrag selbst.
 *
 * @return bool Ob das Formular ausgegeben wurde.
 */
function mmm_render_locked_single(): bool {

	$mmm_post_id = (int) get_the_ID();
	$mmm_plugin  = is_callable( array( 'Mixmymeals_Post_Types', 'is_locked' ) )
		&& is_callable( array( 'Mixmymeals_Post_Types', 'password_form' ) );

	$mmm_locked = $mmm_plugin
		? Mixmymeals_Post_Types::is_locked( $mmm_post_id )
		: post_password_required( $mmm_post_id );

	if ( ! $mmm_locked ) {
		return false;
	}

	$mmm_form = $mmm_plugin
		? Mixmymeals_Post_Types::password_form( $mmm_post_id )
		: get_the_password_form( $mmm_post_id );
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'mmm-single-locked' ); ?>>
		<div class="mmm-plan-page">
			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</header>
			<div class="entry-content">
				<?php echo $mmm_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core's password form. ?>
			</div>
		</div>
	</article>
	<?php

	return true;
}

/**
 * Passwortschutz, der nicht am Template hängt.
 *
 * mmm_render_locked_single() wird aus den Single-Templates dieses Themes
 * gerufen. Sobald eine Einzelansicht aus dem Elementor Theme Builder kommt,
 * läuft die Theme-Datei nicht mehr — und damit auch die Abfrage nicht. Ein
 * geschütztes Rezept oder ein geschützter Plan stünde dann vollständig da:
 * Hero, Zutaten, Zubereitung, Einkaufsliste.
 *
 * WordPress schützt von sich aus nur the_content(); alles, was dieses Theme
 * und das Plugin daneben ausgeben, muss selbst fragen (siehe CLAUDE.md des
 * Plugins, „Core protects the_content() and nothing else"). Deshalb steht die
 * Entscheidung hier, vor dem Template: Ist der Beitrag gesperrt, gibt diese
 * Seite das Formular aus und endet — gleich, wer das Template stellt.
 *
 * Die Templates dürfen ihren eigenen Aufruf behalten; sie kommen nach dieser
 * Weiche gar nicht mehr an die Reihe.
 *
 * @return void
 */
function mmm_guard_locked_singles(): void {

	if ( is_embed() || is_feed() || is_admin() ) {
		return;
	}

	$mmm_types = array( 'mmm_recipe', 'mmm_meal_plan', 'mmm_meal', 'mmm_ingredient', 'mmm_equipment' );

	if ( ! is_singular( $mmm_types ) ) {
		return;
	}

	$mmm_id = (int) get_queried_object_id();

	// Dieselbe Frage wie in mmm_render_locked_single(): das Plugin kennt den
	// Besucher, der das Passwort eingegeben hat, WordPress allein nicht.
	$mmm_locked = is_callable( array( 'Mixmymeals_Post_Types', 'is_locked' ) )
		? Mixmymeals_Post_Types::is_locked( $mmm_id )
		: post_password_required( $mmm_id );

	if ( ! $mmm_locked ) {
		return;
	}

	get_header();

	echo '<div id="primary" ';
	mmm_primary_class();
	echo '>';

	if ( have_posts() ) {
		the_post();
		mmm_render_locked_single();
		rewind_posts();
	}

	echo '</div>';

	get_footer();
	exit;
}
add_action( 'template_redirect', 'mmm_guard_locked_singles', 20 );

/**
 * Die Abschnitte einer Einzelseite ausgeben. Das ist alles, was in den
 * Templates noch steht.
 *
 * @param string $type 'recipe' oder 'meal'.
 */
function mmm_render_single_sections( string $type ): void {

	$registry = mmm_single_section_registry( $type );

	foreach ( mmm_single_section_order( $type ) as $slug ) {

		if ( ! mmm_single_section_is_visible( $type, $slug ) ) {
			continue;
		}

		$render = $registry[ $slug ]['render'] ?? null;

		if ( is_callable( $render ) ) {
			call_user_func( $render );
		}
	}
}

/**
 * Überschrift eines Abschnitts samt Trennlinie.
 */
function mmm_single_section_head( string $heading ): void {
	printf(
		'<div class="mmm-section-head"><h2>%s</h2><span class="mmm-section-head__line"></span></div>',
		esc_html( $heading )
	);
}

/*
==========================================================
	Abschnitte: Rezeptseite
	========================================================== */

/**
 * Beitragsbild. Rezepte ohne Foto erscheinen wie im Kochbuch, nur mit Titel —
 * dann fällt auch der Rahmen weg.
 */
function mmm_recipe_section_feature_image(): void {

	ob_start();
	mmm_render_feature_image_for_a_single_recipe();
	$image = trim( (string) ob_get_clean() );

	if ( '' === $image ) {
		return;
	}
	?>
	<div class="mmm-recipe-feature-image">
		<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php
	/*
	 * Bildnachweis, direkt unter dem Bild. Steht bewusst AUSSERHALB des
	 * Rahmens: der hat overflow:hidden, eine Zeile darin würde abgeschnitten.
	 * (Bis 3.45 hatte er zusätzlich ein festes Seitenverhältnis — das ist weg,
	 * das Bild bringt sein eigenes mit.) Gibt nichts aus, wenn der Nachweis
	 * nicht gepflegt ist.
	 */
	echo do_shortcode( '[mmm_image_credit]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is already escaped.
}

/**
 * Zutaten.
 */
function mmm_recipe_section_ingredients(): void {

	$ingredients = do_shortcode( '[mmm_recipe_ingredients]' );

	if ( '' === trim( $ingredients ) ) {
		return;
	}
	?>
	<section class="mmm-recipe-section">
		<?php mmm_single_section_head( __( 'Ingredients', 'mixmymeals-hello-child' ) ); ?>
		<?php echo $ingredients; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php // Zutaten gesichtet — hier entscheidet man, ob es auf den Plan soll. ?>
		<?php mmm_render_recipe_plan_button(); ?>
	</section>
	<?php
}

/**
 * Benötigte Ausstattung.
 */
function mmm_recipe_section_equipment(): void {

	$equipment = do_shortcode( '[mmm_equipment_required_for_a_single_recipe]' );

	if ( '' === trim( $equipment ) ) {
		return;
	}
	?>
	<section class="mmm-recipe-section">
		<?php mmm_single_section_head( __( 'Required Equipment', 'mixmymeals-hello-child' ) ); ?>
		<?php echo $equipment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}

/**
 * Zubereitung.
 */
function mmm_recipe_section_steps(): void {
	?>
	<section class="mmm-recipe-section">
		<?php mmm_single_section_head( __( 'Cooking Instructions', 'mixmymeals-hello-child' ) ); ?>
		<?php mmm_render_recipe_steps_for_a_single_recipe(); ?>

		<?php // Nach der Zubereitung: zweiter Einstieg in den Planer. ?>
		<?php mmm_render_recipe_plan_button(); ?>
	</section>
	<?php
}

/**
 * Autorenkasten.
 */
function mmm_recipe_section_author(): void {
	mmm_render_recipe_author_box();
}

/**
 * Nährwerte.
 */
function mmm_recipe_section_nutrition(): void {
	?>
	<section class="mmm-recipe-section">
		<?php mmm_single_section_head( __( 'Nutrition Details', 'mixmymeals-hello-child' ) ); ?>
		<?php mmm_render_nutrition_table_for_a_single_recipe(); ?>

		<?php // Nährwerte gesehen — letzter Einstieg auf dem Weg nach unten. ?>
		<?php mmm_render_recipe_plan_button(); ?>
	</section>
	<?php
}

/**
 * Produktwahl.
 *
 * Gibt nichts aus, solange keine Zutat dieses Rezepts mehrere Produkte mit
 * wirklich abweichenden Werten hat.
 *
 * Gleicher Vergleich, gleicher Wortlaut und gleiche Einheit wie bei einer
 * Mahlzeit: [mmm_recipe_product_impact] ist der Mahlzeiten-Shortcode unter
 * eigenem Namen, und eine Mahlzeit löst nur das Rezept auf, das sie einplant —
 * beide Seiten sagen also dasselbe über dasselbe Gericht, je Portion. Zwei
 * Texte dafür würden sich irgendwann widersprechen.
 */
function mmm_recipe_section_product_impact(): void {

	$impact = do_shortcode( '[mmm_recipe_product_impact]' );

	if ( '' === trim( $impact ) ) {
		return;
	}
	?>
	<section class="mmm-recipe-section">
		<?php mmm_single_section_head( __( 'Product choice', 'mixmymeals-hello-child' ) ); ?>
		<p class="mmm-product-impact__intro">
			<?php esc_html_e( 'The choice of a specific product can affect the nutrition of the dish.', 'mixmymeals-hello-child' ); ?>
		</p>
		<?php echo $impact; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		/*
		 * Nur wenn eine Tabelle die Markierung wirklich trägt: eine Zutat ohne
		 * Referenzprodukt bekommt kein Sternchen, und eine Legende zu einem
		 * Sternchen, das nirgends auf der Seite steht, ist schlimmer als keine.
		 */
		if ( str_contains( $impact, 'mmm-product-impact__ref-mark' ) ) :
			?>
			<p class="mmm-product-impact__legend">
				<?php esc_html_e( '* The recipe assumes this product.', 'mixmymeals-hello-child' ); ?>
			</p>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Text aus dem Editor.
 */
function mmm_recipe_section_content(): void {

	if ( '' === trim( get_the_content() ) ) {
		return;
	}
	?>
	<section class="mmm-recipe-section mmm-recipe-content">
		<?php the_content(); ?>
	</section>
	<?php
}

/**
 * „Dazu passt": Rezepte, mit denen dieses kombiniert ist.
 */
function mmm_recipe_section_related(): void {

	$related = do_shortcode( '[mmm_recipe_related]' );

	if ( '' === trim( $related ) ) {
		return;
	}
	?>
	<section class="mmm-recipe-section mmm-recipe-related">
		<?php mmm_single_section_head( __( 'Goes well with', 'mixmymeals-hello-child' ) ); ?>
		<?php echo $related; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}

/**
 * „Passend zu": Rezepte, die dieses als Beilage führen.
 */
function mmm_recipe_section_related_reverse(): void {

	$related = do_shortcode( '[mmm_recipe_related_reverse]' );

	if ( '' === trim( $related ) ) {
		return;
	}
	?>
	<section class="mmm-recipe-section mmm-recipe-related mmm-recipe-related--reverse">
		<?php mmm_single_section_head( __( 'Fits with', 'mixmymeals-hello-child' ) ); ?>
		<?php echo $related; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}

/**
 * Wochenpläne, die dieses Rezept enthalten.
 */
function mmm_recipe_section_plans(): void {

	$plans = do_shortcode( '[mmm_meal_plans_for_a_single_recipe]' );

	if ( '' === trim( $plans ) ) {
		return;
	}
	?>
	<section class="mmm-recipe-section mmm-recipe-plans-section">
		<?php mmm_single_section_head( __( 'Included in these plans', 'mixmymeals-hello-child' ) ); ?>
		<?php echo $plans; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}

/**
 * Hinweiskasten: Haftungsausschluss und Pflegezähler.
 */
function mmm_recipe_section_note(): void {
	?>
	<section class="mmm-meal-section">
		<?php mmm_single_section_head( __( 'Note', 'mixmymeals-hello-child' ) ); ?>
		<?php mmm_render_recipe_disclaimer_for_a_single_recipe(); ?>
		<?php mmm_render_recipe_edit_count(); ?>
	</section>
	<?php
}

/**
 * Community-Fotos: nur freigegebene Besucherfotos. Bleibt verborgen, bis es
 * ein Foto oder einen Upload-Button gibt.
 */
function mmm_recipe_section_photos(): void {

	$photos = do_shortcode( '[mmm_recipe_photo_gallery]' );

	if ( '' === trim( $photos ) ) {
		return;
	}
	?>
	<section class="mmm-recipe-section mmm-recipe-photos">
		<?php mmm_single_section_head( __( 'Cooked by You', 'mixmymeals-hello-child' ) ); ?>
		<?php echo $photos; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}

/**
 * Bewertungen: eigene Liste plus schlankes Formular statt der Blog-Kommentare,
 * damit das als Rezeptbewertung liest.
 */
function mmm_recipe_section_ratings(): void {
	?>
	<section class="mmm-recipe-section mmm-recipe-ratings">
		<?php mmm_single_section_head( __( 'Ratings', 'mixmymeals' ) ); ?>
		<?php
		echo do_shortcode( '[mmm_recipe_rating]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo do_shortcode( '[mmm_recipe_reviews]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		comment_form();
		?>
	</section>
	<?php
}

/*
==========================================================
	Abschnitte: Mahlzeitenseite
	========================================================== */

/**
 * Beitragsbild. Mahlzeiten zu Rezepten ohne Foto erscheinen wie im Kochbuch,
 * nur mit Titel — dann fällt der Rahmen weg.
 */
function mmm_meal_section_feature_image(): void {

	ob_start();
	mmm_render_feature_image_for_a_single_meal();
	$image = trim( (string) ob_get_clean() );

	if ( '' === $image ) {
		return;
	}
	?>
	<div class="mmm-meal-feature-image">
		<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php
	/*
	 * Das Bild gehört zum Rezept, also löst auch der Nachweis auf das Rezept
	 * auf. Außerhalb des Rahmens — Begründung siehe Rezeptseite.
	 */
	echo do_shortcode( '[mmm_image_credit]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is already escaped.
}

/**
 * Zutaten, auf die Portionen der Mahlzeit gerechnet.
 */
function mmm_meal_section_ingredients(): void {
	?>
	<section class="mmm-meal-section">
		<?php mmm_single_section_head( __( 'Ingredients', 'mixmymeals-hello-child' ) ); ?>
		<?php mmm_render_ingredients_a_single_meal(); ?>
	</section>
	<?php
}

/**
 * Benötigte Ausstattung.
 */
function mmm_meal_section_equipment(): void {

	$equipment = do_shortcode( '[mmm_equipment_required_for_a_single_meal]' );

	if ( '' === trim( $equipment ) ) {
		return;
	}
	?>
	<section class="mmm-meal-section">
		<?php mmm_single_section_head( __( 'Required Equipment', 'mixmymeals-hello-child' ) ); ?>
		<?php echo $equipment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}

/**
 * Zubereitung samt Kochmodus.
 */
function mmm_meal_section_steps(): void {
	?>
	<section class="mmm-meal-section">
		<?php mmm_single_section_head( __( 'Cooking Instructions', 'mixmymeals-hello-child' ) ); ?>
		<?php
		mmm_render_cook_mode_button_for_a_single_meal();
		mmm_render_scaled_recipe_steps_for_a_single_meal();
		?>
	</section>
	<?php
}

/**
 * Autorenkasten des Rezepts, das diese Mahlzeit einplant.
 */
function mmm_meal_section_author(): void {
	mmm_render_recipe_author_box_for_a_single_meal();
}

/**
 * Nährwerte.
 */
function mmm_meal_section_nutrition(): void {
	?>
	<section class="mmm-meal-section">
		<?php mmm_single_section_head( __( 'Nutrition Details', 'mixmymeals-hello-child' ) ); ?>
		<?php mmm_render_meal_nutrition_table_for_a_single_meal(); ?>
	</section>
	<?php
}

/**
 * Produktwahl.
 *
 * Gibt nichts aus, solange keine Zutat dieses Gerichts mehrere Produkte mit
 * wirklich abweichenden Werten hat. Je Portion, nicht je Plantag: diese Seite
 * ist das Gericht.
 */
function mmm_meal_section_product_impact(): void {

	$impact = do_shortcode( '[mmm_meal_product_impact]' );

	if ( '' === trim( $impact ) ) {
		return;
	}
	?>
	<section class="mmm-meal-section">
		<?php mmm_single_section_head( __( 'Product choice', 'mixmymeals-hello-child' ) ); ?>
		<p class="mmm-product-impact__intro">
			<?php esc_html_e( 'The choice of a specific product can affect the nutrition of the dish.', 'mixmymeals-hello-child' ); ?>
		</p>
		<?php echo $impact; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		/*
		 * Nur wenn eine Tabelle die Markierung wirklich trägt: eine Zutat ohne
		 * Referenzprodukt bekommt kein Sternchen, und eine Legende zu einem
		 * Sternchen, das nirgends auf der Seite steht, ist schlimmer als keine.
		 */
		if ( str_contains( $impact, 'mmm-product-impact__ref-mark' ) ) :
			?>
			<p class="mmm-product-impact__legend">
				<?php esc_html_e( '* The recipe assumes this product.', 'mixmymeals-hello-child' ); ?>
			</p>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Verzehrrechner.
 */
function mmm_meal_section_calculator(): void {
	?>
	<section class="mmm-meal-section">
		<?php mmm_single_section_head( __( 'How much did you eat?', 'mixmymeals-hello-child' ) ); ?>
		<?php mmm_render_meal_calculator_for_a_single_meal(); ?>
	</section>
	<?php
}

/**
 * Hinweiskasten: Haftungsausschluss.
 */
function mmm_meal_section_note(): void {
	?>
	<section class="mmm-meal-section">
		<?php mmm_single_section_head( __( 'Note', 'mixmymeals-hello-child' ) ); ?>
		<?php mmm_render_recipe_disclaimer_for_a_single_meal(); ?>
	</section>
	<?php
}

/**
 * Text aus dem Editor. Ohne eigenen Abschnitt, wie bisher.
 */
function mmm_meal_section_content(): void {
	the_content();
}

/*
==========================================================
	Abschnitte: Planseite
	========================================================== */

/**
 * Autorenkasten. Die eigenen Autorenfelder des Plans gewinnen, sonst wird die
 * WordPress-Autorin genannt.
 */
function mmm_plan_section_author(): void {

	if ( ! shortcode_exists( 'mmm_meal_plan_author_box' ) ) {
		return;
	}

	echo do_shortcode( '[mmm_meal_plan_author_box]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is already escaped.
}

/**
 * Beitragsbild: nur das eigene Bild des Plans, kein Rückgriff auf ein Rezept.
 * Ohne eigenes Bild bleibt die Seite ohne Bild.
 */
function mmm_plan_section_feature_image(): void {

	if ( ! has_post_thumbnail() ) {
		return;
	}
	?>
	<div class="mmm-plan-feature-image">
		<?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?>
	</div>
	<?php
	// Bildnachweis, direkt unter dem Bild. Außerhalb des Rahmens, wie auf
	// Rezept- und Mahlzeitenseite.
	echo do_shortcode( '[mmm_image_credit]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is already escaped.
}

/**
 * Die Mahlzeiten des Plans.
 */
function mmm_plan_section_meals(): void {
	?>
	<section class="mmm-plan-section">
		<?php mmm_single_section_head( __( 'Meals in this plan', 'mixmymeals-hello-child' ) ); ?>
		<?php mmm_render_meal_plan_meals(); ?>
	</section>
	<?php
}

/**
 * Nährwertübersicht des Plans.
 */
function mmm_plan_section_nutrition(): void {
	?>
	<section class="mmm-plan-section">
		<?php mmm_single_section_head( __( 'Nutrition Overview', 'mixmymeals-hello-child' ) ); ?>
		<?php echo do_shortcode( '[mmm_nutrition_overview_for_a_single_meal_plan]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}

/**
 * Einkaufsliste.
 */
function mmm_plan_section_shopping_list(): void {
	?>
	<section class="mmm-plan-section">
		<?php mmm_single_section_head( __( 'Shopping List', 'mixmymeals-hello-child' ) ); ?>
		<?php mmm_render_meal_plan_shopping_list(); ?>
	</section>
	<?php
}

/**
 * Produktwahl.
 *
 * Standardmäßig direkt hinter der Einkaufsliste: dort hat man gerade gelesen,
 * dass 500 g Joghurt zu kaufen sind, und hier steht, welcher Joghurt eine
 * Überlegung wert ist. Gibt nichts aus, solange keine Zutat des Plans mehrere
 * Produkte mit wirklich abweichenden Werten hat.
 */
function mmm_plan_section_product_impact(): void {

	$impact = do_shortcode( '[mmm_meal_plan_product_impact]' );

	if ( '' === trim( $impact ) ) {
		return;
	}
	?>
	<section class="mmm-plan-section">
		<?php mmm_single_section_head( __( 'Product choice', 'mixmymeals-hello-child' ) ); ?>
		<p class="mmm-product-impact__intro">
			<?php esc_html_e( 'Some ingredients of this plan can be bought as more than one product. This is what each choice would do to the daily nutrition per person.', 'mixmymeals-hello-child' ); ?>
		</p>
		<?php echo $impact; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		/*
		 * Nur wenn eine Tabelle die Markierung wirklich trägt: eine Zutat ohne
		 * Referenzprodukt bekommt kein Sternchen, und eine Legende zu einem
		 * Sternchen, das nirgends auf der Seite steht, ist schlimmer als keine.
		 */
		if ( str_contains( $impact, 'mmm-product-impact__ref-mark' ) ) :
			?>
			<p class="mmm-product-impact__legend">
				<?php esc_html_e( '* The plan assumes this product.', 'mixmymeals-hello-child' ); ?>
			</p>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Ausstattung über alle Mahlzeiten des Plans. Nur, wenn ein Rezept überhaupt
 * Ausstattung führt.
 */
function mmm_plan_section_equipment(): void {

	$equipment = do_shortcode( '[mmm_equipment_required_for_a_single_meal_plan]' );

	if ( '' === trim( $equipment ) ) {
		return;
	}
	?>
	<section class="mmm-plan-section">
		<?php mmm_single_section_head( __( 'Required Equipment', 'mixmymeals-hello-child' ) ); ?>
		<?php echo $equipment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is already escaped. ?>
	</section>
	<?php
}

/**
 * Abo-Formular. Erscheint nur, wenn der Shortcode etwas ausgibt (Abos
 * eingeschaltet, Zeitplan vorhanden, Plan gültig).
 */
function mmm_plan_section_subscribe(): void {
	/*
	 * Der Anker für „Abonnieren“ im Hero sitzt an diesem Abschnitt, nicht am
	 * Formular: der Sprung soll auf der Überschrift landen und nicht im ersten
	 * Eingabefeld, über dem dann nichts mehr zu sehen wäre. Nur um diesen
	 * Aufruf gelegt, damit ein Formular an anderer Stelle seine id behält —
	 * und damit die id auf der Seite eindeutig bleibt.
	 */
	add_filter( 'mixmymeals_subscribe_form_anchor', '__return_empty_string' );
	$subscribe = do_shortcode( '[mmm_meal_plan_subscribe pick_start="1"]' );
	remove_filter( 'mixmymeals_subscribe_form_anchor', '__return_empty_string' );

	if ( '' === trim( $subscribe ) ) {
		return;
	}

	/*
	 * Ohne den Filter oben trüge das Formular die id. Kennt das Plugin die
	 * Konstante nicht (vor 3.73.0), gibt es auch keinen Button, der hierher
	 * zeigt — dann bleibt die id einfach weg.
	 */
	$mmm_anchor = defined( 'Mixmymeals_Subscriptions::FORM_ANCHOR' )
		? (string) Mixmymeals_Subscriptions::FORM_ANCHOR
		: '';
	?>
	<section class="mmm-plan-section"<?php echo '' !== $mmm_anchor ? ' id="' . esc_attr( $mmm_anchor ) . '"' : ''; ?>>
		<?php mmm_single_section_head( __( 'Get this plan by e-mail', 'mixmymeals-hello-child' ) ); ?>
		<p class="mmm-subscribe-intro"><?php esc_html_e( 'Subscribe and get the meals of this plan sent to your inbox, day by day.', 'mixmymeals-hello-child' ); ?></p>
		<?php echo $subscribe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is already escaped. ?>
	</section>
	<?php
}

/**
 * Text aus dem Editor.
 */
function mmm_plan_section_content(): void {

	if ( '' === trim( get_the_content() ) ) {
		return;
	}
	?>
	<section class="mmm-plan-section mmm-plan-section--intro">
		<?php the_content(); ?>
	</section>
	<?php
}
