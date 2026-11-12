<?php
/**
 * MixMyMeals Hello Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package MixMyMeals Hello Child
 * @since 1.0.0
 */

define( 'MMM_CHILD_THEME_VERSION', '2.0.0' );

require_once 'includes/layout.php';
require_once 'includes/theme-settings.php';
require_once 'includes/shortcodes.php';
require_once 'includes/meal_plan_archive.php';
require_once 'includes/single_meal_plan.php';
require_once 'includes/plan-actions.php';
require_once 'includes/navigation.php';
require_once 'includes/recipe_archive.php';
require_once 'includes/single_recipe.php';
require_once 'includes/single_meal.php';
require_once 'includes/single-sections.php';
require_once 'includes/archive-sections.php';
require_once 'includes/custom-sections.php';
require_once 'includes/search.php';
require_once 'includes/blog.php';
require_once 'includes/redesign.php';

/**
 * Enqueue styles.
 */
function child_enqueue_styles() {

	/*
	 * Versioned by the file's own timestamp, not by the constant.
	 *
	 * The constant had not moved since the initial commit while style.css was
	 * edited dozens of times, so every change went out behind the same
	 * ?ver=1.5.1 and browsers kept serving the copy they already had. A theme
	 * whose only stylesheet changes with every feature cannot rely on somebody
	 * remembering to bump a number; the plugin versions its admin CSS the same
	 * way for the same reason.
	 */
	$mmm_css     = get_stylesheet_directory() . '/style.css';
	$mmm_version = file_exists( $mmm_css )
		? (string) filemtime( $mmm_css )
		: MMM_CHILD_THEME_VERSION;

	/*
	 * Schriften und Grundregeln kommen aus dem Child-Theme selbst (bis 1.x
	 * lieferte beides Astra): assets/fonts/fonts.css und assets/css/base.css.
	 * style.css baut darauf auf und wird deshalb danach geladen.
	 */
	$mmm_dir = get_stylesheet_directory();
	$mmm_uri = get_stylesheet_directory_uri();

	foreach ( array(
		'mmm-fonts' => '/assets/fonts/fonts.css',
		'mmm-base'  => '/assets/css/base.css',
	) as $mmm_handle => $mmm_file ) {
		wp_enqueue_style(
			$mmm_handle,
			$mmm_uri . $mmm_file,
			array(),
			file_exists( $mmm_dir . $mmm_file ) ? (string) filemtime( $mmm_dir . $mmm_file ) : MMM_CHILD_THEME_VERSION
		);
	}

	wp_enqueue_style(
		'mixmymeals-hello-child-theme-css',
		$mmm_uri . '/' . mmm_stylesheet_file( $mmm_css ),
		array( 'mmm-fonts', 'mmm-base' ),
		$mmm_version,
		'all'
	);
}

/*
 * Die eigenen Grundregeln ersetzen die von Hello Elementor (reset.css und
 * theme.css): zwei Sätze Element-Regeln übereinander ergäben ein Mischbild,
 * und die Seiten sollen aussehen wie vor dem Wechsel des Eltern-Themes.
 */
add_filter( 'hello_elementor_enqueue_style', '__return_false' );
add_filter( 'hello_elementor_enqueue_theme_style', '__return_false' );
add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/**
 * Which file carries the theme's styles: style.min.css when it was built from
 * the style.css on disk, else style.css itself.
 *
 * style.css is the source and stays the file WordPress reads the theme header
 * from; tools/minify-css.php builds the minified copy (a third of the bytes —
 * PageSpeed counted the stylesheet as the largest render-blocking request).
 * The copy records the hash of its source in its first line, so a style.css
 * edited without a rebuild is served as it is rather than an outdated copy.
 * SCRIPT_DEBUG serves the source, as for the plugin's scripts.
 *
 * The hash is computed once per change of either file (cached by mtime and
 * size), not on every request.
 *
 * @param string $source Absolute path of style.css.
 * @return string File name relative to the theme directory.
 */
function mmm_stylesheet_file( string $source ): string {

	$minified = dirname( $source ) . '/style.min.css';

	if ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ! is_readable( $source ) || ! is_readable( $minified ) ) {
		return 'style.css';
	}

	$key   = 'mmm_style_min_' . md5( filemtime( $source ) . ':' . filesize( $source ) . ':' . filemtime( $minified ) );
	$fresh = get_transient( $key );

	if ( false === $fresh ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
		$head  = (string) file_get_contents( $minified, false, null, 0, 64 );
		$fresh = ( 0 === strpos( $head, '/* src:' . md5_file( $source ) . ' */' ) ) ? 'yes' : 'no';
		set_transient( $key, $fresh, DAY_IN_SECONDS );
	}

	return 'yes' === $fresh ? 'style.min.css' : 'style.css';
}

/**
 * Load local FontAwesome stylesheet.
 */
function mixmymeals_enqueue_fontawesome_local() {
	wp_enqueue_style(
		'font-awesome-local',
		get_stylesheet_directory_uri() . '/assets/fontawesome/fontawesome-free-7.1.0-web/css/all.min.css',
		array()
	);
}
add_action( 'wp_enqueue_scripts', 'mixmymeals_enqueue_fontawesome_local' );

/**
 * Load text domain.
 */
function mmm_child_load_textdomain() {
	load_child_theme_textdomain(
		'mixmymeals-hello-child',
		get_stylesheet_directory() . '/languages'
	);
}
add_action( 'after_setup_theme', 'mmm_child_load_textdomain' );
// add_action( 'init', 'mmm_child_load_textdomain' );

/**
 * Bootstrap.
 */
// In case MixMyMeals plugin is active...
if ( class_exists( 'mixmymeals' ) ) {

	// ... add meta to posts in meal plan archive:
	add_action(
		'mmm_card_meta_after',
		'mmm_add_meta_to_meal_plan_archive_posts',
		20
	);

	// ... add weekday overview, in one row with the plan's number, to posts in meal plan archive:
	add_action(
		'mmm_card_title_before',
		'mmm_add_weekdays_and_number_to_meal_plan_archive_posts',
		20
	);

	// ... add meal type overview to posts in meal plan archive:
	add_action(
		'mmm_card_title_before',
		'mmm_add_meal_type_overview_meal_plan_archive_posts',
		30
	);

	// ... add thumbnails to posts in meal plan archive:
	add_action(
		'mmm_card_title_after',
		'mmm_add_thumbnails_to_meal_plan_archive_posts',
		20
	);

	// ... add meta to posts in a recipe archive:
	add_action(
		'mmm_card_meta_after',
		'mmm_add_meta_to_recipe_archive_posts',
		20
	);

	// ... add the average rating below the meta in a recipe archive:
	add_action(
		'mmm_card_title_after',
		'mmm_add_rating_to_recipe_archive_posts',
		25
	);

	// ... add the cart action below the description in a recipe archive:
	add_action(
		'mmm_card_content_after',
		'mmm_add_actions_to_recipe_archive_posts',
		20
	);

	// ... add search form above the recipe/meal plan archive list:
	// Hooked to mmm_after_archive_title (not _description): the active Astra
	// banner layout fires the title hook reliably, the description hook does not.
	add_action(
		'mmm_after_archive_title',
		'mmm_add_archive_search_to_archive',
		30
	);

	// ... add recipe category terms below the recipe archive list:
	add_action(
		'mmm_primary_content_bottom',
		'mmm_add_recipe_category_terms_to_recipe_archive',
		20
	);

	// ... provide the {{ logo_html }} placeholder (site logo) for plugin e-mails:
	add_filter( 'mixmymeals_mail_default_vars', 'mmm_email_logo_var' );

	// ... provide the {{ *_style }} placeholders (buttons, muted text) for plugin e-mails:
	add_filter( 'mixmymeals_mail_default_vars', 'mmm_email_style_vars' );

	// ... give the headings in plugin e-mails their size inline:
	add_filter( 'mixmymeals_mail_args', 'mmm_email_inline_heading_styles' );

	// ... hand the plugin's mail markup the brand colours (its own are neutral):
	add_filter( 'mixmymeals_mail_colors', 'mmm_email_colors' );

	// ... word the headings inside the planner's boxes (page copy is the theme's):
	add_filter( 'mixmymeals_planner_heading', 'mmm_planner_heading', 10, 2 );

	// NOTE: The former post_thumbnail_id filter (mmm_force_placeholder_thumbnail)
	// was removed: recipes without a photo now render cookbook-style (title
	// only). The placeholder image is only used as an explicit fallback for
	// social previews (SEO) and Instagram slide backgrounds in the plugin.

	// ... print the archive descriptions:
	add_action(
		'mmm_after_archive_title',
		'mmm_print_archive_description_from_option'
	);

	// ... print the archive descriptions:
	add_action(
		'mmm_after_archive_description',
		'mmm_print_meal_plan_category_filter'
	);

	// ... use meal plan archive as homepage:
	// add_action( 'pre_get_posts',
	// 'mmm_use_meal_plan_archive_as_home_page' );

}

/**
 * Wording of the headings inside the planner's boxes.
 *
 * Here and not in page-templates/planer.php, because the nutrition and
 * shopping boxes are re-rendered over admin-ajax, which loads no template.
 *
 * @param string $text Default heading from the plugin.
 * @param string $key  'unassigned', 'nutrition' or 'shopping'.
 * @return string
 */
function mmm_planner_heading( $text, $key ) {

	$headings = array(
		'unassigned' => __( 'Unassigned Recipes', 'mixmymeals-hello-child' ),
		'nutrition'  => __( 'Average nutrition per day and person', 'mixmymeals-hello-child' ),
		'shopping'   => __( 'Shopping list', 'mixmymeals-hello-child' ),
	);

	return $headings[ $key ] ?? $text;
}

/**
 * The brand's colours for e-mails, by the plugin's colour roles.
 *
 * One palette for both sides of a mail: the plugin's own markup (shopping
 * list, nutrition table, buttons, the plan number — through the
 * mixmymeals_mail_colors filter, since its defaults are neutral) and the
 * placeholders and headings this theme adds below. Mail clients reach no
 * stylesheet, so these cannot come from style.css.
 *
 * @param array $colors Colours by role (unused: the brand replaces them all).
 * @return array<string, string>
 */
function mmm_email_colors( $colors = array() ) {

	return array(
		'heading'   => '#3F6F1E', // green-700
		'accent'    => '#5C9E2E', // green-500
		'on_accent' => '#ffffff',
		'surface'   => '#ffffff',
		'rule'      => '#ECE6D9', // cream border
		'text'      => '#2C2A24',
		'muted'     => '#6B665B',
		'subtle'    => '#8A8478',
		'link'      => '#E8A33D', // accent orange
	);
}

/**
 * Adds a {{ logo_html }} (and {{ logo_url }}) placeholder to the plugin's
 * subscription e-mail templates.
 *
 * Always prints the site name; the site's Custom Logo (Customizer → Logo) sits
 * left of it when one is set. A logo alone left the mail without a word saying
 * who sent it. Both cells link to the site. Laid out as a table and styled
 * inline, since mail clients drop flexbox and, in some views, the <style> block.
 *
 * @param array $vars Existing default placeholders.
 * @return array
 */
function mmm_email_logo_var( $vars ) {

	$site_name = isset( $vars['site_name'] )
		? (string) $vars['site_name']
		: wp_specialchars_decode( (string) get_option( 'blogname' ), ENT_QUOTES );
	$site_url  = isset( $vars['site_url'] ) ? (string) $vars['site_url'] : home_url( '/' );

	$logo_id  = (int) get_theme_mod( 'custom_logo' );
	$logo_url = $logo_id ? (string) wp_get_attachment_image_url( $logo_id, 'full' ) : '';

	$c     = mmm_email_colors();
	$link  = '<a class="mmm-mail-logo-link" href="' . esc_url( $site_url ) . '" style="text-decoration:none;">';
	$brand = $link . '<span class="mmm-mail-brand" style="font-family:Fredoka,Helvetica,Arial,sans-serif;font-size:22px;font-weight:600;color:' . $c['heading'] . ';">'
		. esc_html( $site_name ) . '</span></a>';

	$cells = '';
	if ( '' !== $logo_url ) {
		$cells .= '<td style="vertical-align:middle;padding-right:12px;">' . $link
			. '<img class="mmm-mail-logo" src="' . esc_url( $logo_url ) . '" alt="" height="48" style="display:block;height:48px;width:auto;border:0;"></a></td>';
	}
	$cells .= '<td style="vertical-align:middle;">' . $brand . '</td>';

	$vars['logo_url']  = $logo_url;
	$vars['logo_html'] = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;"><tr>'
		. $cells . '</tr></table>';

	return $vars;
}

/**
 * Adds inline-style placeholders to the plugin's subscription e-mail templates,
 * so a template writes style="{{ button_style }}" instead of repeating the
 * whole declaration. Inline, because Gmail and Outlook drop the layout's
 * <style> block in some views.
 *
 * The values are rendered through esc_html(), so they must not contain quotes.
 *
 * @param array $vars Existing default placeholders.
 * @return array
 */
function mmm_email_style_vars( $vars ) {

	$c    = mmm_email_colors();
	$pill = 'display:inline-block;padding:11px 18px;border-radius:999px;font-weight:800;text-decoration:none;';

	$vars['button_style']         = $pill . 'background:' . $c['accent'] . ';color:' . $c['on_accent'] . ';border:2px solid ' . $c['accent'] . ';';
	$vars['button_outline_style'] = $pill . 'background:' . $c['surface'] . ';color:' . $c['accent'] . ';border:2px solid ' . $c['accent'] . ';';
	$vars['muted_style']          = 'color:' . $c['muted'] . ';';

	// The plan number: the line that carries the small label above a heading,
	// and the giant one of the welcome mail — the same frame, scaled up.
	$vars['plan_number_line_style']      = 'margin:0 0 8px;text-align:right;';
	$vars['plan_number_hero_line_style'] = 'margin:8px 0 28px;text-align:center;';
	$vars['plan_number_hero_style']      = 'display:inline-block;padding:6px 20px;border:4px solid ' . $c['heading'] . ';border-radius:14px;'
		. 'background:' . $c['surface'] . ';color:' . $c['heading'] . ';font-family:Fredoka,Helvetica,Arial,sans-serif;'
		. 'font-size:64px;line-height:1.05;font-weight:700;letter-spacing:0.02em;white-space:nowrap;';

	return $vars;
}

/**
 * Sets the size of every bare <h1>/<h2> in a subscription mail inline.
 *
 * The templates write plain headings and the layout's <style> block sizes
 * them — but Gmail and Outlook drop that block in some views, and the client
 * then falls back to its own 32px/24px, against 16px body text. Inlining it
 * here keeps the templates free of style attributes on every heading.
 *
 * Only tags without attributes are touched, so a heading that brings its own
 * style (the plugin's ingredient categories, say) keeps it. Only mails built
 * on this theme's layout are touched at all.
 *
 * Scale shared with layout.html: 26 / 20 / 16 (body) / 14 / 12.
 *
 * @param array $mail wp_mail() arguments.
 * @return array
 */
function mmm_email_inline_heading_styles( $mail ) {

	if ( empty( $mail['message'] ) || false === strpos( (string) $mail['message'], 'mmm-mail-body' ) ) {
		return $mail;
	}

	$font = 'font-family:Fredoka,Helvetica,Arial,sans-serif;font-weight:600;color:' . mmm_email_colors()['heading'] . ';';

	$mail['message'] = str_replace(
		array( '<h1>', '<h2>' ),
		array(
			'<h1 style="' . $font . 'font-size:26px;line-height:1.25;margin:0 0 16px;">',
			'<h2 style="' . $font . 'font-size:20px;line-height:1.3;margin:28px 0 10px;">',
		),
		(string) $mail['message']
	);

	return $mail;
}


/*
 * Prints archive descriptions depending on post type.
 */
function mmm_print_archive_description_from_option() {

	// A filtered view of the recipe archive describes itself, the way a term
	// description does on a tag archive — browse view, diet or time alike. The
	// general recipe intro below would say nothing about the list on screen,
	// and for the diet and time views the note carries what the list leaves
	// out.
	if ( function_exists( 'mmm_archive_facet' ) && ! empty( mmm_archive_facet() ) ) {

		$mmm_note = mmm_archive_facet_text( 1 );

		if ( '' !== $mmm_note ) {
			echo '<div class="mmm-archive-description mmm-archive-description-mmm_recipe">';
			echo wp_kses_post( wpautop( $mmm_note ) );
			echo '</div>';
		}

		return;
	}

	// Taxonomy archives: mirror the matching post-type archive hero. Print the
	// term's own description when it has one, otherwise the global intro, in the
	// same styled block at this hook. That ordering matters — the search form
	// hooks the same action at priority 30, so a description printed here always
	// sits above it. Leaving it to Astra would render it AFTER the search form
	// instead, which is why the taxonomy archives disagreed with the post-type
	// archives. Astra's duplicate is suppressed via
	// mmm_clear_tax_archive_description().
	$tax_map = array(
		'mmm_recipe_category'    => array( 'mmm_recipe', 'mixmymeals_all_recipes_description' ),
		'mmm_recipe_tag'         => array( 'mmm_recipe', 'mixmymeals_all_recipes_description' ),
		'mmm_meal_plan_category' => array( 'mmm_meal_plan', 'mixmymeals_all_meal_plans_description' ),
		'mmm_meal_plan_tag'      => array( 'mmm_meal_plan', 'mixmymeals_all_meal_plans_description' ),
	);

	foreach ( $tax_map as $taxonomy => $target ) {

		if ( ! is_tax( $taxonomy ) ) {
			continue;
		}

		list( $post_type, $option_key ) = $target;

		$term_desc   = trim( (string) term_description() );
		$description = '' !== $term_desc
			? $term_desc
			: get_option( $option_key );

		if ( empty( $description ) ) {
			return;
		}

		echo '<div class="mmm-archive-description mmm-archive-description-'
			. esc_attr( $post_type ) . '">';
		echo wp_kses_post( wpautop( $description ) );
		echo '</div>';

		return;
	}

	// Mapping: Post Type → Option
	$map = array(
		'mmm_meal_plan'  => 'mixmymeals_all_meal_plans_description',
		'mmm_recipe'     => 'mixmymeals_all_recipes_description',
		'mmm_ingredient' => 'mixmymeals_all_ingredients_description',
		'mmm_equipment'  => 'mixmymeals_all_equipment_description',
	);

	foreach ( $map as $post_type => $option_key ) {

		if ( ! is_post_type_archive( $post_type ) ) {
			continue;
		}

		if ( $post_type === 'mmm_meal_plan' && get_query_var( 'mmm_uncategorized' ) ) {
			$option_key = 'mixmymeals_individual_meal_plans_description';
		}

		$description = get_option( $option_key );

		if ( empty( $description ) ) {
			return;
		}

		echo '<div class="mmm-archive-description mmm-archive-description-'
			. esc_attr( $post_type ) . '">';
		echo wp_kses_post( wpautop( $description ) );
		echo '</div>';

		return;
	}
}

/*
 * Loads meal plan archive on trying to access the homepage. To be used with an action.
 */
/*
function mmm_use_meal_plan_archive_as_home_page( $wp_query ) {
	if ( is_admin() || ! $wp_query->is_main_query() ) {
		return;
	}
	// Wenn die aktuelle Startseite geladen wird
	if ( $wp_query->get( 'page_id' ) == get_option( 'page_on_front' ) ) {
		$wp_query->set( 'post_type', 'mmm_meal_plan' );   // CPT-Slug einsetzen
		$wp_query->set( 'page_id', '' );
		$wp_query->is_page = false;
		$wp_query->is_singular = false;
		$wp_query->is_post_type_archive = true;
		$wp_query->is_archive = true;

		// Optional: Pagination korrekt setzen
		$paged = ! empty( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		$wp_query->set( 'paged', $paged );
	}
}
*/



/**
 * Adds a shared body class to every recipe archive — the post-type archive as
 * well as the recipe category and tag taxonomy archives — so the recipe-archive
 * styling applies uniformly instead of only on .post-type-archive-mmm_recipe.
 *
 * @param string[] $classes Existing body classes.
 * @return string[]
 */
function mmm_recipe_archive_body_class( $classes ) {
	if (
		is_post_type_archive( 'mmm_recipe' )
		|| is_tax( array( 'mmm_recipe_category', 'mmm_recipe_tag' ) )
	) {
		$classes[] = 'mmm-recipe-archive';
	}
	return $classes;
}
add_filter( 'body_class', 'mmm_recipe_archive_body_class' );

/**
 * Suppresses Astra's own archive-description output on the recipe and meal plan
 * category/tag archives. The styled hero intro is printed by
 * mmm_print_archive_description_from_option() instead, so the hero matches the
 * matching post-type archive exactly and the description is not shown twice.
 *
 * @param string $description Archive description from Astra/core.
 * @return string
 */
function mmm_clear_tax_archive_description( $description ) {
	if (
		is_tax(
			array(
				'mmm_recipe_category',
				'mmm_recipe_tag',
				'mmm_meal_plan_category',
				'mmm_meal_plan_tag',
			)
		)
	) {
		return '';
	}
	return $description;
}
add_filter( 'get_the_archive_description', 'mmm_clear_tax_archive_description' );




/**
 * Social / contact platforms shown in the recipe author box.
 *
 * Each entry: a user-meta key → label, FontAwesome icon class and value type
 * ('url' or 'email'). The built-in WordPress "Website" field (user_url) is
 * rendered separately and is not listed here.
 *
 * @return array<string,array{label:string,icon:string,type:string}>
 */
function mmm_author_social_platforms() {
	return array(
		'mmm_public_email' => array(
			'label' => __( 'Contact e-mail', 'mixmymeals-hello-child' ),
			'icon'  => 'fa-solid fa-envelope',
			'type'  => 'email',
		),
		'mmm_x'            => array(
			'label' => 'X (Twitter)',
			'icon'  => 'fa-brands fa-x-twitter',
			'type'  => 'url',
		),
		'mmm_instagram'    => array(
			'label' => 'Instagram',
			'icon'  => 'fa-brands fa-instagram',
			'type'  => 'url',
		),
		'mmm_facebook'     => array(
			'label' => 'Facebook',
			'icon'  => 'fa-brands fa-facebook',
			'type'  => 'url',
		),
		'mmm_youtube'      => array(
			'label' => 'YouTube',
			'icon'  => 'fa-brands fa-youtube',
			'type'  => 'url',
		),
		'mmm_tiktok'       => array(
			'label' => 'TikTok',
			'icon'  => 'fa-brands fa-tiktok',
			'type'  => 'url',
		),
		'mmm_pinterest'    => array(
			'label' => 'Pinterest',
			'icon'  => 'fa-brands fa-pinterest',
			'type'  => 'url',
		),
		'mmm_linkedin'     => array(
			'label' => 'LinkedIn',
			'icon'  => 'fa-brands fa-linkedin',
			'type'  => 'url',
		),
		'mmm_mastodon'     => array(
			'label' => 'Mastodon',
			'icon'  => 'fa-brands fa-mastodon',
			'type'  => 'url',
		),
	);
}

/**
 * Registers the social/contact fields on the user profile screen so authors can
 * fill them in (Users → Profile → "Contact Info"). Stores full URLs; the e-mail
 * field stores a public contact address (kept separate from the login e-mail).
 *
 * @param array $methods Existing contact methods.
 * @return array
 */
function mmm_author_contactmethods( $methods ) {
	foreach ( mmm_author_social_platforms() as $key => $cfg ) {
		$methods[ $key ] = 'email' === $cfg['type']
			? $cfg['label']
			/* translators: %s: platform name (e.g. Instagram). */
			: sprintf( __( '%s URL', 'mixmymeals-hello-child' ), $cfg['label'] );
	}
	return $methods;
}
add_filter( 'user_contactmethods', 'mmm_author_contactmethods' );

/**
 * Builds the social/contact link row (icon links) for an author from their
 * profile fields. Returns '' when the author has filled in none.
 *
 * @param int $author_id Author user ID.
 * @return string
 */
function mmm_author_social_links_html( $author_id ) {

	$links = array();

	// Built-in website field first.
	$website = (string) get_the_author_meta( 'user_url', $author_id );
	if ( '' !== trim( $website ) ) {
		$links[] = array(
			'href'  => esc_url( $website ),
			'icon'  => 'fa-solid fa-globe',
			'label' => __( 'Website', 'mixmymeals-hello-child' ),
		);
	}

	foreach ( mmm_author_social_platforms() as $key => $cfg ) {
		$value = trim( (string) get_the_author_meta( $key, $author_id ) );
		if ( '' === $value ) {
			continue;
		}

		if ( 'email' === $cfg['type'] ) {
			if ( ! is_email( $value ) ) {
				continue;
			}
			$href = 'mailto:' . antispambot( $value );
		} else {
			$href = esc_url( $value );
			if ( '' === $href ) {
				continue;
			}
		}

		$links[] = array(
			'href'  => $href,
			'icon'  => $cfg['icon'],
			'label' => $cfg['label'],
		);
	}

	return mmm_author_social_links_markup( $links );
}

/**
 * Renders the icon row for a prepared list of links. Shared by the
 * profile-based and the external-author variant so both are identical.
 *
 * @param array $links List of href/icon/label entries.
 * @return string
 */
function mmm_author_social_links_markup( $links ) {

	if ( empty( $links ) ) {
		return '';
	}

	$html = '<div class="mmm-author-box__social">';
	foreach ( $links as $link ) {
		$is_mail = 0 === strpos( $link['href'], 'mailto:' );
		$attrs   = $is_mail ? '' : ' target="_blank" rel="me noopener noreferrer"';
		$html   .= '<a class="mmm-author-box__social-link" href="' . esc_attr( $link['href'] ) . '"'
			. $attrs . ' title="' . esc_attr( $link['label'] ) . '">'
			. '<i class="' . esc_attr( $link['icon'] ) . '" aria-hidden="true"></i>'
			. '<span class="screen-reader-text">' . esc_html( $link['label'] ) . '</span>'
			. '</a>';
	}
	$html .= '</div>';

	return $html;
}

/**
 * Maps a URL or e-mail address to a platform icon and label. Unknown hosts fall
 * back to a globe, so an author can link anywhere.
 *
 * @param string $address URL or e-mail address.
 * @return array{href:string,icon:string,label:string}|null
 */
function mmm_author_social_link_from_address( $address ) {

	$address = trim( (string) $address );

	if ( '' === $address ) {
		return null;
	}

	if ( is_email( $address ) ) {
		return array(
			'href'  => 'mailto:' . antispambot( $address ),
			'icon'  => 'fa-solid fa-envelope',
			'label' => __( 'Contact e-mail', 'mixmymeals-hello-child' ),
		);
	}

	$href = esc_url( $address );

	if ( '' === $href ) {
		return null;
	}

	$host = strtolower( (string) wp_parse_url( $href, PHP_URL_HOST ) );
	$host = preg_replace( '/^www\./', '', $host );

	// Host fragment → [icon, label]. Checked in order, first match wins.
	$known = array(
		'x.com'         => array( 'fa-brands fa-x-twitter', 'X (Twitter)' ),
		'twitter.com'   => array( 'fa-brands fa-x-twitter', 'X (Twitter)' ),
		'instagram.com' => array( 'fa-brands fa-instagram', 'Instagram' ),
		'facebook.com'  => array( 'fa-brands fa-facebook', 'Facebook' ),
		'youtube.com'   => array( 'fa-brands fa-youtube', 'YouTube' ),
		'youtu.be'      => array( 'fa-brands fa-youtube', 'YouTube' ),
		'tiktok.com'    => array( 'fa-brands fa-tiktok', 'TikTok' ),
		'pinterest.'    => array( 'fa-brands fa-pinterest', 'Pinterest' ),
		'linkedin.com'  => array( 'fa-brands fa-linkedin', 'LinkedIn' ),
		'mastodon'      => array( 'fa-brands fa-mastodon', 'Mastodon' ),
		'threads.'      => array( 'fa-brands fa-threads', 'Threads' ),
		'bsky.'         => array( 'fa-brands fa-bluesky', 'Bluesky' ),
	);

	foreach ( $known as $needle => $cfg ) {
		if ( false !== strpos( $host, $needle ) ) {
			return array(
				'href'  => $href,
				'icon'  => $cfg[0],
				'label' => $cfg[1],
			);
		}
	}

	return array(
		'href'  => $href,
		'icon'  => 'fa-solid fa-globe',
		'label' => '' !== $host ? $host : __( 'Website', 'mixmymeals-hello-child' ),
	);
}

/**
 * Builds the social link row from a plain list of addresses (external authors).
 * Same markup as the profile-based row, so both look identical.
 *
 * @param string[] $addresses URLs and/or e-mail addresses.
 * @return string
 */
function mmm_author_social_links_html_from_addresses( $addresses ) {

	$links = array();

	foreach ( (array) $addresses as $address ) {
		$link = mmm_author_social_link_from_address( $address );

		if ( null !== $link ) {
			$links[] = $link;
		}
	}

	return mmm_author_social_links_markup( $links );
}

/**
 * Injects the author's social/contact links into the recipe and meal plan
 * author boxes just inside the card body, below the bio. Keeps the plugin
 * shortcodes untouched.
 *
 * Two sources, matching the box itself: an external author's own address list
 * from the post's author fields, otherwise the WordPress author's profile
 * fields. Never mixed — a profile's channels must not end up under an external
 * author's name.
 *
 * @param string $output Shortcode output.
 * @param string $tag    Shortcode tag.
 * @param array  $attr   Shortcode attributes.
 * @return string
 */
function mmm_inject_author_social_links( $output, $tag, $attr ) {

	$boxes = array( 'mmm_recipe_author_box', 'mmm_meal_plan_author_box' );

	if ( ! in_array( $tag, $boxes, true ) || '' === trim( (string) $output ) ) {
		return $output;
	}

	if ( ! class_exists( 'Mixmymeals_Post_Meta' ) || ! class_exists( 'Mixmymeals_Meal' ) ) {
		return $output;
	}

	$post_id = ( isset( $attr['id'] ) && (int) $attr['id'] > 0 ) ? (int) $attr['id'] : (int) get_the_ID();

	// On a meal the box shows the recipe's author, so the extras have to come
	// from the recipe too — reading the meal here would append the WordPress
	// profile of whoever created the meal.
	$post_id = Mixmymeals_Meal::resolve_credit_source( $post_id );

	if ( ! $post_id ) {
		return $output;
	}

	if ( Mixmymeals_Post_Meta::has_external_author( $post_id ) ) {
		$social = mmm_author_social_links_html_from_addresses(
			Mixmymeals_Post_Meta::get_author_social( $post_id )
		);

		if ( '' === $social ) {
			return $output;
		}

		$pos = strrpos( $output, '</div>' );

		return false === $pos
			? $output
			: substr( $output, 0, $pos ) . $social . substr( $output, $pos );
	}

	$author_id = (int) get_post_field( 'post_author', $post_id );
	if ( $author_id <= 0 ) {
		return $output;
	}

	$social = mmm_author_social_links_html( $author_id );
	if ( '' === $social ) {
		return $output;
	}

	// The card body is the last <div> in the markup (followed only by </aside>),
	// so inserting before the final </div> places the links inside the body.
	$pos = strrpos( $output, '</div>' );
	if ( false === $pos ) {
		return $output;
	}

	return substr( $output, 0, $pos ) . $social . substr( $output, $pos );
}
add_filter( 'do_shortcode_tag', 'mmm_inject_author_social_links', 10, 3 );
