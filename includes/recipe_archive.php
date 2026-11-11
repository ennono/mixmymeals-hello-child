<?php

function mmm_add_meta_to_recipe_archive_posts() {

	if ( shortcode_exists( 'mmm_meta_for_posts_in_recipe_archive' ) ) {
		echo do_shortcode( '[mmm_meta_for_posts_in_recipe_archive]' );
	}
}

/**
 * The wording of the four browse views, in the order they are rendered.
 * Die Favoriten stehen oben: wer das Archiv zum ersten Mal sieht, soll
 * zuerst das Beste sehen, nicht das Neueste.
 *
 * Slug => [ shortcode, heading, one-line note ]. The slugs come from the
 * plugin (Mixmymeals_Recipe::browse_views()), which owns what each view
 * selects; the wording is the theme's, and this is the only place it exists.
 * The same array feeds the rows on the archive and the heading above a
 * filtered archive, so the link and the page it leads to cannot end up with
 * two different names for one thing.
 *
 * @return array<string, array{0:string,1:string,2:string}>
 */
function mmm_recipe_browse_views() {

	return array(
		'fav'      => array(
			'mmm_favorite_recipes',
			__( 'Our Favorites', 'mixmymeals-astra-child' ),
			__( 'Recipes we cook again and again and can particularly recommend to you.', 'mixmymeals-astra-child' ),
		),
		'new'      => array(
			'mmm_new_recipes',
			__( 'New recipes', 'mixmymeals-astra-child' ),
			__( 'The most recently published recipes — everything new lands here.', 'mixmymeals-astra-child' ),
		),
		'popular'  => array(
			'mmm_popular_recipes',
			__( 'Frequently Cooked', 'mixmymeals-astra-child' ),
			__( 'These recipes have been on the meal plans most often so far.', 'mixmymeals-astra-child' ),
		),
		'uncooked' => array(
			'mmm_uncooked_recipes',
			__( 'Never cooked', 'mixmymeals-astra-child' ),
			__( 'Nobody has planned these recipes yet — maybe your new favorite is among them.', 'mixmymeals-astra-child' ),
		),
	);
}

/**
 * The browse view the archive is currently narrowed to, or '' for the plain
 * archive. Asks the plugin, which validates the parameter before answering.
 *
 * @return string
 */
function mmm_archive_view() {

	// Only here. The parameter could be sitting on any URL, and a view name is
	// not allowed to retitle a page that is not this archive.
	if ( ! class_exists( 'Mixmymeals_Recipe' ) || ! is_post_type_archive( 'mmm_recipe' ) ) {
		return '';
	}

	$mmm_view = Mixmymeals_Recipe::current_browse_view();

	return isset( mmm_recipe_browse_views()[ $mmm_view ] ) ? $mmm_view : '';
}

/**
 * Wording of the diet views: heading and the one line under it.
 *
 * The plugin owns the slug, the query and the index; the theme owns what the
 * page says — the same split as mmm_recipe_browse_views(), so a wording exists
 * once.
 *
 * The note is not decoration here. The filter collects what a dish **can** be
 * cooked as, so a recipe may appear with the pill "Vegan-capable" rather than
 * "Vegan", and a page that does not say so reads as a broken filter.
 *
 * @return array<string, array{0:string,1:string}>
 */
function mmm_recipe_diet_views() {

	return array(
		'vegan'        => array(
			__( 'Vegan recipes', 'mixmymeals-astra-child' ),
			__( 'Dishes that can be cooked vegan — for some of them the product you buy decides.', 'mixmymeals-astra-child' ),
		),
		'vegetarian'   => array(
			__( 'Vegetarian recipes', 'mixmymeals-astra-child' ),
			__( 'Dishes that can be cooked vegetarian — for some of them the product you buy decides.', 'mixmymeals-astra-child' ),
		),
		'gluten_free'  => array(
			__( 'Gluten-free recipes', 'mixmymeals-astra-child' ),
			__( 'Dishes that can be cooked gluten-free — for some of them the product you buy decides.', 'mixmymeals-astra-child' ),
		),
		'lactose_free' => array(
			__( 'Lactose-free recipes', 'mixmymeals-astra-child' ),
			__( 'Dishes that can be cooked lactose-free — for some of them the product you buy decides.', 'mixmymeals-astra-child' ),
		),
	);
}

/**
 * Wording of the time views.
 *
 * The note says what the list leaves out, because "under 30 minutes" silently
 * dropping every recipe nobody has timed is the kind of gap a visitor reads as
 * a missing recipe rather than a missing figure.
 *
 * @return array<int, array{0:string,1:string}>
 */
function mmm_recipe_time_views() {

	$mmm_views = array();

	// The steps are the plugin's (filter mixmymeals_prep_time_steps); a list
	// of its own here would leave a step the site adds without a heading.
	$mmm_steps = is_callable( array( 'Mixmymeals_Recipe', 'prep_time_steps' ) )
		? Mixmymeals_Recipe::prep_time_steps()
		: array();

	foreach ( $mmm_steps as $mmm_minutes ) {
		$mmm_views[ $mmm_minutes ] = array(
			sprintf(
				/* translators: %d: number of minutes. */
				__( 'Ready in under %d minutes', 'mixmymeals-astra-child' ),
				$mmm_minutes
			),
			__( 'Recipes without a stated time are not listed here.', 'mixmymeals-astra-child' ),
		);
	}

	return $mmm_views;
}

/**
 * The facet that owns this page, as [ kind, value ], or an empty array.
 *
 * Asked of the plugin rather than worked out again: the form's action, the
 * pills and this heading have to agree about which filter the path stands for.
 *
 * @return array{0:string,1:mixed}|array{}
 */
function mmm_archive_facet() {

	if ( ! class_exists( 'Mixmymeals_Shortcodes' ) || ! is_post_type_archive( 'mmm_recipe' ) ) {
		return array();
	}

	$mmm_filters = Mixmymeals_Shortcodes::archive_filters();
	$mmm_kind    = Mixmymeals_Shortcodes::archive_primary_facet( $mmm_filters );

	return '' === $mmm_kind ? array() : array( $mmm_kind, $mmm_filters[ $mmm_kind ] );
}

/**
 * Heading and note of whatever facet this page shows.
 *
 * @param int $mmm_index 0 for the heading, 1 for the note.
 * @return string
 */
function mmm_archive_facet_text( $mmm_index ) {

	$mmm_facet = mmm_archive_facet();

	if ( empty( $mmm_facet ) ) {
		return '';
	}

	list( $mmm_kind, $mmm_value ) = $mmm_facet;

	if ( 'diet' === $mmm_kind ) {
		$mmm_views = mmm_recipe_diet_views();

		// The wording is keyed by the plugin's former built-in keys; the term
		// carrying "vegetarian" may be called "vegetarisch" today.
		if ( ! isset( $mmm_views[ $mmm_value ] ) && is_callable( array( 'Mixmymeals_Diet', 'former_key' ) ) ) {
			$mmm_key   = Mixmymeals_Diet::former_key( (string) $mmm_value );
			$mmm_value = '' !== $mmm_key ? $mmm_key : $mmm_value;
		}
	} elseif ( 'time' === $mmm_kind ) {
		$mmm_views = mmm_recipe_time_views();
	} else {
		$mmm_views = array();

		foreach ( mmm_recipe_browse_views() as $mmm_slug => $mmm_view ) {
			$mmm_views[ $mmm_slug ] = array( $mmm_view[1], $mmm_view[2] );
		}
	}

	return isset( $mmm_views[ $mmm_value ][ $mmm_index ] ) ? $mmm_views[ $mmm_value ][ $mmm_index ] : '';
}

/**
 * The heading for a filtered archive — the same words the row carried.
 *
 * @param string $mmm_view View slug.
 * @return string
 */
function mmm_archive_view_title( $mmm_view ) {

	$mmm_views = mmm_recipe_browse_views();

	return isset( $mmm_views[ $mmm_view ] ) ? $mmm_views[ $mmm_view ][1] : '';
}

/**
 * The one-line explanation of a view — what a term description is on a tag
 * archive.
 *
 * @param string $mmm_view View slug.
 * @return string
 */
function mmm_archive_view_description( $mmm_view ) {

	$mmm_views = mmm_recipe_browse_views();

	return isset( $mmm_views[ $mmm_view ] ) ? $mmm_views[ $mmm_view ][2] : '';
}

/**
 * Makes a browse view read as its own archive: the view's name becomes the
 * page title, exactly as a tag archive shows its term.
 *
 * Without this the heading says "Rezepte" on all four of them, and the only
 * thing telling a visitor which list they are looking at is the URL.
 *
 * @param string $mmm_title Archive title from Astra/core.
 * @return string
 */
function mmm_browse_view_archive_title( $mmm_title ) {

	$mmm_facet_title = mmm_archive_facet_text( 0 );

	return '' !== $mmm_facet_title ? $mmm_facet_title : $mmm_title;
}
add_filter( 'get_the_archive_title', 'mmm_browse_view_archive_title', 30 );

/**
 * The same name in the browser tab and in search results.
 *
 * A post type archive titles itself after the post type, so all four views
 * would share one title — which is what a tag archive never does.
 *
 * @param array $mmm_parts Document title parts.
 * @return array
 */
function mmm_browse_view_document_title( $mmm_parts ) {

	$mmm_facet_title = mmm_archive_facet_text( 0 );

	if ( '' !== $mmm_facet_title ) {
		$mmm_parts['title'] = $mmm_facet_title;
	}

	return $mmm_parts;
}
add_filter( 'document_title_parts', 'mmm_browse_view_document_title' );

/**
 * The same name in the tab when Yoast writes the title.
 *
 * Yoast builds the document title from its own template and never runs
 * document_title_parts, so the filter above is dead on this site: every facet
 * page called itself "Alle Rezepte" in the tab and in search results while the
 * heading on the page said "Vegane Rezepte". Same situation as the robots tags,
 * which the plugin also has to hand Yoast separately.
 *
 * Only replaces the title, never removes Yoast's suffix — that part is the
 * site's own setting.
 *
 * @param string $mmm_title Title Yoast assembled.
 * @return string
 */
function mmm_browse_view_yoast_title( $mmm_title ) {

	$mmm_facet_title = mmm_archive_facet_text( 0 );

	if ( '' === $mmm_facet_title ) {
		return $mmm_title;
	}

	// Yoast's own title for this archive, with the facet's name put in its
	// place: "Alle Rezepte - stopfnicht.de" becomes "Vegane Rezepte - …".
	$mmm_archive = post_type_archive_title( '', false );

	if ( '' !== $mmm_archive && false !== strpos( $mmm_title, $mmm_archive ) ) {
		return str_replace( $mmm_archive, $mmm_facet_title, $mmm_title );
	}

	return $mmm_facet_title;
}
add_filter( 'wpseo_title', 'mmm_browse_view_yoast_title', 20 );

/**
 * What one active filter is called.
 *
 * The plugin says what is filtered, the theme says how it reads — the same
 * split as mmm_recipe_browse_views(), so a wording exists once.
 *
 * @param string $mmm_kind  'diet', 'time', 'view', 'ingredient' or 'title'.
 * @param string $mmm_value Its value.
 * @return string
 */
function mmm_filter_label( $mmm_kind, $mmm_value ) {

	switch ( $mmm_kind ) {

		case 'diet':
			return class_exists( 'Mixmymeals_Diet' ) ? Mixmymeals_Diet::label( $mmm_value ) : $mmm_value;

		case 'time':
			return sprintf(
				/* translators: %d: number of minutes. */
				__( 'under %d min', 'mixmymeals-astra-child' ),
				(int) $mmm_value
			);

		case 'view':
			return mmm_archive_view_title( $mmm_value );

		case 'title':
			return sprintf(
				/* translators: %s: the search term. */
				__( 'Title: %s', 'mixmymeals-astra-child' ),
				$mmm_value
			);

		default:
			return $mmm_value;
	}
}

/**
 * The filters in force, in words, for the empty state.
 *
 * @return string[]
 */
function mmm_active_filter_labels() {

	if ( ! class_exists( 'Mixmymeals_Shortcodes' ) ) {
		return array();
	}

	$mmm_labels = array();

	foreach ( Mixmymeals_Shortcodes::active_archive_filters() as $mmm_filter ) {

		$mmm_label = mmm_filter_label( $mmm_filter['kind'], $mmm_filter['value'] );

		if ( '' !== $mmm_label ) {
			$mmm_labels[] = $mmm_label;
		}
	}

	return $mmm_labels;
}

/**
 * The row of chips naming every filter in force, each one removable.
 *
 * The heading names the primary facet only, and the search fields hold their
 * own values — so with two filters running there was nowhere on the page that
 * said what the list was actually narrowed by, and no way to loosen one of them
 * without editing the address. A chip per filter answers both.
 *
 * Deliberately links, not buttons: each one is an address the archive already
 * answers, so it works without JavaScript and can be opened in a new tab.
 *
 * @return void
 */
function mmm_print_active_filter_chips() {

	if ( ! class_exists( 'Mixmymeals_Shortcodes' ) ) {
		return;
	}

	$mmm_filters = Mixmymeals_Shortcodes::active_archive_filters();

	if ( empty( $mmm_filters ) ) {
		return;
	}

	$mmm_reset = get_post_type_archive_link( 'mmm_recipe' );

	?>
	<div class="mmm-active-filters">
		<span class="mmm-active-filters__label"><?php esc_html_e( 'Filtered by:', 'mixmymeals-astra-child' ); ?></span>
		<?php foreach ( $mmm_filters as $mmm_filter ) : ?>
			<?php
			$mmm_label = mmm_filter_label( $mmm_filter['kind'], $mmm_filter['value'] );

			if ( '' === $mmm_label || '' === $mmm_filter['url'] ) {
				continue;
			}

			/* translators: %s: the filter that would be removed. */
			$mmm_remove_title = sprintf( __( 'Remove filter “%s”', 'mixmymeals-astra-child' ), $mmm_label );
			?>
			<a class="mmm-active-filters__chip mmm-active-filters__chip--<?php echo esc_attr( $mmm_filter['kind'] ); ?>"
				href="<?php echo esc_url( $mmm_filter['url'] ); ?>"
				title="<?php echo esc_attr( $mmm_remove_title ); ?>">
				<span class="mmm-active-filters__text"><?php echo esc_html( $mmm_label ); ?></span>
				<span class="mmm-active-filters__remove" aria-hidden="true">&times;</span>
			</a>
		<?php endforeach; ?>
		<?php if ( count( $mmm_filters ) > 1 && $mmm_reset ) : ?>
			<a class="mmm-active-filters__reset" href="<?php echo esc_url( $mmm_reset ); ?>">
				<?php esc_html_e( 'Clear all', 'mixmymeals-astra-child' ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * The ingredient names nobody on this site has ever heard of.
 *
 * The most useful thing an empty result can say: a typo is a far likelier
 * explanation than an empty larder.
 *
 * @return string[]
 */
function mmm_unknown_ingredient_tokens() {

	if ( ! class_exists( 'Mixmymeals_Shortcodes' ) ) {
		return array();
	}

	$mmm_tokens = Mixmymeals_Shortcodes::archive_filters()['ingredients'];

	if ( empty( $mmm_tokens ) ) {
		return array();
	}

	return Mixmymeals_Shortcodes::resolve_ingredient_tokens( $mmm_tokens )['unknown'];
}

/*
 * The way back to the unfiltered list used to be a single "All recipes" link
 * here. The chip row does that and more — every filter is named and each one
 * can be dropped on its own — so one link doing a worse version of the same
 * thing beside it would only be a second thing to read.
 */
add_action( 'astra_after_archive_description', 'mmm_print_active_filter_chips', 5 );

/**
 * The active archive search term, or '' when nobody is searching.
 *
 * [mmm_archive_search] submits `mmm_s` and the plugin filters the main query
 * with it, deliberately keeping the page a post-type archive — WP's own `s`
 * would switch templates. The consequence is that nothing here notices a search
 * unless it looks, which is why this exists and why it is read in one place.
 *
 * @return string
 */
function mmm_archive_search_term() {

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only public GET filter, same as the plugin's own handling.
	$mmm_s = isset( $_GET['mmm_s'] ) ? wp_unslash( $_GET['mmm_s'] ) : '';

	return trim( sanitize_text_field( $mmm_s ) );
}

/**
 * The empty state for a search that found nothing.
 *
 * Astra cannot supply a usable one here. The archive search keeps the page a
 * post-type archive, so is_search() is false, and Astra falls into its generic
 * branch: a "nothing found" sentence plus get_search_form() — a *second* search
 * box, sitting under the one the visitor just used, and searching the whole
 * site over `s` rather than recipes over `mmm_s`. Nothing on the page says what
 * was searched for, and nothing offers a way back.
 *
 * @param string $mmm_term The search term.
 * @return void
 */
function mmm_render_recipe_search_empty_state( $mmm_term ) {

	$mmm_filters = mmm_active_filter_labels();
	$mmm_unknown = mmm_unknown_ingredient_tokens();

	?>
	<section class="mmm-archive-empty">
		<h2 class="mmm-archive-empty__title">
			<?php
			if ( '' !== $mmm_term ) {
				printf(
					/* translators: %s: the search term. */
					esc_html__( 'No recipe matches “%s”', 'mixmymeals-astra-child' ),
					esc_html( $mmm_term )
				);
			} else {
				esc_html_e( 'No recipe matches these filters', 'mixmymeals-astra-child' );
			}
			?>
		</h2>
		<?php if ( ! empty( $mmm_unknown ) ) : ?>
			<p class="mmm-archive-empty__text">
				<?php
				printf(
					/* translators: %s: comma separated list of ingredient names. */
					esc_html__( 'We do not know these ingredients: %s', 'mixmymeals-astra-child' ),
					esc_html( implode( ', ', $mmm_unknown ) )
				);
				?>
			</p>
		<?php endif; ?>
		<?php if ( ! empty( $mmm_filters ) ) : ?>
			<p class="mmm-archive-empty__text">
				<?php
				printf(
					/* translators: %s: comma separated list of the active filters. */
					esc_html__( 'Active: %s. Dropping one of them usually helps.', 'mixmymeals-astra-child' ),
					esc_html( implode( ', ', $mmm_filters ) )
				);
				?>
			</p>
		<?php else : ?>
			<p class="mmm-archive-empty__text">
				<?php esc_html_e( 'The title search looks at titles only. A shorter term, or a different word, often helps.', 'mixmymeals-astra-child' ); ?>
			</p>
		<?php endif; ?>
		<p class="mmm-archive-empty__actions">
			<a class="mmm-archive-empty__reset" href="<?php echo esc_url( get_post_type_archive_link( 'mmm_recipe' ) ); ?>">
				<?php esc_html_e( 'Show all recipes', 'mixmymeals-astra-child' ); ?>
			</a>
		</p>
	</section>
	<?php
}

/**
 * Renders the "browse" card (newest + favorite recipes) between the hero and the
 * recipe list on the main recipe post-type archive. Called from
 * archive-mmm_recipe.php so it sits in its own card outside the hero. Each
 * shortcode renders '' when it has nothing to show, so empty sections disappear
 * and the whole card is omitted when both are empty.
 */
function mmm_render_recipe_browse_card() {

	if ( ! is_post_type_archive( 'mmm_recipe' ) ) {
		return;
	}

	// Only on the first page. From page two on the visitor is already working
	// through the list; the browse rows would just push the results down and
	// repeat recipes that are listed below anyway.
	if ( is_paged() ) {
		return;
	}

	// Nothing but results while a search is running. Somebody who typed a term
	// asked a question, and four rows of suggestions above the answer are in
	// the way — the more so because those rows list recipes that do not match
	// what was searched for.
	if ( '' !== mmm_archive_search_term() ) {
		return;
	}

	// Same for any active filter: the visitor followed one of these rows, or
	// picked a diet, a time or an ingredient, to see that list — putting all
	// four rows back above the result undoes the click.
	//
	// Asked as "is anything filtered", not "does a filter own the path":
	// several diets own no path (they travel as ?diaet=a,b), and the rows came
	// back for exactly that case. The chip row asks the same question.
	if ( mmm_archive_facet() || ( class_exists( 'Mixmymeals_Shortcodes' )
		&& Mixmymeals_Shortcodes::active_archive_filters() ) ) {
		return;
	}

	// Each shortcode renders its own self-contained, boxed browse section
	// (heading + note + horizontal track), so the same shortcodes can be
	// dropped onto any page (e.g. the front page). The theme supplies the
	// wording; the plugin links the heading to the archive showing that view
	// and drops the link when the row already holds everything there is.
	// Empty shortcodes render '' and disappear cleanly.
	foreach ( mmm_recipe_browse_views() as $mmm_row ) {

		list( $mmm_shortcode, $mmm_title, $mmm_note ) = $mmm_row;

		if ( ! shortcode_exists( $mmm_shortcode ) ) {
			continue;
		}

		echo do_shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is escaped by the plugin.
			sprintf(
				'[%s title="%s" note="%s"]',
				$mmm_shortcode,
				esc_attr( $mmm_title ),
				esc_attr( $mmm_note )
			)
		);
	}

	// The "all categories" card sits directly below the browse rows (i.e. under
	// the last one, "Never cooked"). On taxonomy archives, where there is no
	// browse card, it is rendered at the bottom instead (see the hook below).
	mmm_render_recipe_category_terms_card();
}

/**
 * Renders the "All categories" terms card (a boxed recipe-category term list).
 * Shared by the browse card (post-type archive) and the bottom hook (taxonomy
 * archives). Renders nothing when the shortcode is unavailable or empty.
 */
function mmm_render_recipe_category_terms_card() {

	if ( ! shortcode_exists( 'mmm_recipe_category_terms' ) ) {
		return;
	}

	$mmm_terms = do_shortcode( '[mmm_recipe_category_terms hide_empty=1 top_level_link=0]' );
	if ( '' === trim( $mmm_terms ) ) {
		return;
	}

	// Wrap in a card so the term list isn't bare on the background
	// (scoped class — the same list is also used unboxed in the footer).
	echo '<section id="mmm-all-categories" class="mmm-archive-terms">'
		. '<h2 class="mmm-archive-terms__title">' . esc_html__( 'All categories', 'mixmymeals-astra-child' ) . '</h2>'
		. '<p class="mmm-browse-note">'
		. esc_html__( 'Browse the whole collection by course, cuisine or main ingredient.', 'mixmymeals-astra-child' )
		. '</p>'
		. $mmm_terms // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output is already escaped.
		. '</section>';
}

/**
 * Replaces Astra's "nothing found" block with ours on a fruitless recipe search.
 *
 * Done by swapping the callbacks on Astra's own hook rather than by overriding
 * template-parts/content-none.php in the child theme. That override is the
 * textbook approach and it worked locally — but it needs a new file in a new
 * directory, and a deployment that only carries changed files leaves it behind.
 * The symptom is subtle: everything else on the page updates while the empty
 * state silently stays Astra's.
 *
 * Astra fires astra_template_parts_content_none inside <main id="main">, so
 * whatever runs here lands where its own block would have. The .ast-row wrapper
 * is added because Astra opens it only in the have_posts() branch, and without
 * it the card sits one level shallower than the recipe cards it replaces.
 *
 * @return void
 */
function mmm_swap_recipe_search_empty_state() {

	if ( is_admin() || ! function_exists( 'mmm_archive_search_term' ) ) {
		return;
	}

	if ( '' === mmm_archive_search_term() && empty( mmm_active_filter_labels() ) ) {
		return;
	}

	$mmm_on_recipes = is_post_type_archive( 'mmm_recipe' )
		|| is_tax( array( 'mmm_recipe_category', 'mmm_recipe_tag' ) );

	if ( ! $mmm_on_recipes ) {
		return;
	}

	/*
	 * Only ours from here on. Astra attaches its content-none part and its 404
	 * part to this hook; neither belongs on a search that simply found nothing,
	 * and a 404 never reaches this branch because the condition above requires
	 * a recipe archive.
	 */
	remove_all_actions( 'astra_template_parts_content_none' );

	add_action(
		'astra_template_parts_content_none',
		static function () {
			echo '<div class="ast-row">';
			mmm_render_recipe_search_empty_state( mmm_archive_search_term() );
			echo '</div>';
		}
	);
}
add_action( 'wp', 'mmm_swap_recipe_search_empty_state' );

/**
 * Drops WordPress's generic search form from the empty state on the archives
 * that carry [mmm_archive_search].
 *
 * It searches the whole site over `s` while the box above it searches recipe
 * titles over `mmm_s`. Two search fields on one page that behave differently is
 * worse than one.
 *
 * @param string $mmm_form The form markup.
 * @return string
 */
function mmm_drop_generic_search_form_on_archives( $mmm_form ) {

	$mmm_ours = is_post_type_archive( array( 'mmm_recipe', 'mmm_meal_plan' ) )
		|| is_tax( array( 'mmm_recipe_category', 'mmm_recipe_tag', 'mmm_meal_plan_category', 'mmm_meal_plan_tag' ) );

	return $mmm_ours ? '' : $mmm_form;
}
add_filter( 'get_search_form', 'mmm_drop_generic_search_form_on_archives' );

function mmm_add_rating_to_recipe_archive_posts() {

	if ( ! is_post_type_archive( 'mmm_recipe' ) && ! is_tax( array( 'mmm_recipe_category', 'mmm_recipe_tag' ) ) ) {
		return;
	}

	if ( shortcode_exists( 'mmm_recipe_rating' ) ) {
		echo do_shortcode( '[mmm_recipe_rating hide_empty=1]' );
	}
}

function mmm_add_actions_to_recipe_archive_posts() {

	if ( ! is_post_type_archive( 'mmm_recipe' ) && ! is_tax( array( 'mmm_recipe_category', 'mmm_recipe_tag' ) ) ) {
		return;
	}

	if ( shortcode_exists( 'mmm_recipe_actions' ) ) {
		echo do_shortcode( '[mmm_recipe_actions]' );
	}
}

function mmm_add_archive_search_to_archive() {

	$on_recipe_archive = is_post_type_archive( 'mmm_recipe' )
		|| is_tax( array( 'mmm_recipe_category', 'mmm_recipe_tag' ) );

	$on_meal_plan_archive = is_post_type_archive( 'mmm_meal_plan' )
		|| is_tax( array( 'mmm_meal_plan_category', 'mmm_meal_plan_tag' ) );

	if ( ! $on_recipe_archive && ! $on_meal_plan_archive ) {
		return;
	}

	if ( shortcode_exists( 'mmm_archive_search' ) ) {
		echo do_shortcode( '[mmm_archive_search]' );
	}
}

function mmm_add_recipe_category_terms_to_recipe_archive() {

	// On the main recipe post-type archive the card is rendered inside the browse
	// card (under "Frequently Planned"), so here it only handles the taxonomy
	// archives, where it stays at the bottom below the term's recipe list.
	if ( ! is_tax( array( 'mmm_recipe_category', 'mmm_recipe_tag' ) ) ) {
		return;
	}

	// Same reason as on the post-type archive: while a search is running, the
	// page shows results and nothing else.
	if ( '' !== mmm_archive_search_term() ) {
		return;
	}

	mmm_render_recipe_category_terms_card();
}
