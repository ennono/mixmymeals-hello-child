<?php
/**
 * Layout: die Seitenhülle, die bis Version 1.x das Eltern-Theme Astra stellte.
 *
 * Hello Elementor bringt bewusst fast nichts mit: Header und Footer kommen
 * aus dem Theme Builder von Elementor Pro, der Inhaltsbereich aus den
 * Templates dieses Themes. Was die Templates dafür brauchen, steht hier:
 * die Klassen von #primary, Brotkrumen, der Kopf der Archive, die Schleife
 * mit ihren Karten, die Blätterlinks und die Body-Klassen.
 *
 * Das Markup entspricht dem, was Astra ausgab, damit das vorhandene CSS
 * unverändert greift. Aus dem Präfix ast- wurde mmm-l- (l für Layout), aus
 * den Astra-Hooks wurden eigene:
 *
 *   mmm_primary_content_top / _bottom   am Anfang und Ende von #primary
 *   mmm_after_archive_title             im Archivkopf nach der H1
 *   mmm_after_archive_description       im Archivkopf nach der Beschreibung
 *   mmm_card_title_before / _after      in der Karte um den Titel
 *   mmm_card_meta_after                 in der Karte nach der Meta-Zeile
 *   mmm_card_content_after              in der Karte im Inhaltsblock
 *   mmm_content_none                    statt der Liste, wenn sie leer ist
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ob die Seite ihre Breite selbst bestimmt (Einzelansichten mit eigenem
 * Hero, Seiten aus Elementor) statt im Container des Themes zu sitzen.
 *
 * @return bool
 */
function mmm_layout_is_full_width(): bool {

	if ( is_singular( array( 'mmm_meal_plan', 'mmm_recipe', 'mmm_meal', 'mmm_ingredient', 'mmm_equipment', 'post' ) ) ) {
		return true;
	}

	if ( is_page() ) {
		$template = (string) get_page_template_slug();
		if ( in_array( $template, array( 'elementor_header_footer', 'elementor_canvas' ), true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Body-Klassen, an denen das CSS die Art der Seite erkennt.
 *
 * @param string[] $classes Body-Klassen.
 * @return string[]
 */
function mmm_layout_body_class( $classes ) {

	$classes[] = 'mmm-l-no-sidebar';
	$classes[] = mmm_layout_is_full_width() ? 'mmm-l-page-builder-template' : 'mmm-l-plain-container';

	if ( is_singular() ) {
		$classes[] = 'mmm-l-single-post';
		$classes[] = 'mmm-l-normal-title-enabled';
	}

	if ( is_single() ) {
		$classes[] = 'mmm-l-blog-single-style-1';
		if ( 'post' !== get_post_type() ) {
			$classes[] = 'mmm-l-custom-post-type';
		}
	}

	return $classes;
}
add_filter( 'body_class', 'mmm_layout_body_class' );

/**
 * Der Artikel einer Einzelansicht trägt die Klasse, an der das CSS den
 * Inhalt einer Einzelseite erkennt (Listenabstände, Links).
 *
 * @param string[] $classes Klassen des Artikels.
 * @return string[]
 */
function mmm_layout_post_class( $classes ) {

	if ( is_singular() && get_the_ID() === get_queried_object_id() && ! in_array( 'mmm-l-article-single', $classes, true ) ) {
		$classes[] = 'mmm-l-article-single';
	}

	// Seiten sind keine Einträge einer Liste (hAtom), wie bisher.
	if ( 'page' === get_post_type() ) {
		$classes = array_values( array_diff( $classes, array( 'hentry' ) ) );
	}

	return $classes;
}
add_filter( 'post_class', 'mmm_layout_post_class' );

/**
 * Schema-Typ der Seite für das <body>, wie bisher: Blog für Listen und
 * Einzelbeiträge, sonst WebPage.
 *
 * @return string
 */
function mmm_layout_body_itemtype(): string {

	if ( is_search() ) {
		return 'https://schema.org/SearchResultsPage';
	}

	if ( is_home() || is_archive() || is_attachment() || is_tax() || is_single() ) {
		return 'https://schema.org/Blog';
	}

	return 'https://schema.org/WebPage';
}

/**
 * Klassen von #primary. Listen tragen zusätzlich das Karten-Raster.
 */
function mmm_primary_class(): void {

	$classes = array( 'content-area', 'primary' );

	if ( is_archive() || is_home() || is_search() ) {
		$classes[] = 'mmm-l-grid-3';
		$classes[] = 'mmm-l-blog-layout-5-grid';
	}

	echo 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
}

/**
 * Anfang von #primary.
 */
function mmm_primary_content_top(): void {
	do_action( 'mmm_primary_content_top' );
}

/**
 * Ende von #primary.
 */
function mmm_primary_content_bottom(): void {
	do_action( 'mmm_primary_content_bottom' );
}

/**
 * Die Stationen des Pfads zur aktuellen Seite: [ Beschriftung, Adresse ].
 * Die letzte Station ist die Seite selbst und trägt keine Adresse.
 *
 * @return array<int, array{0:string,1:string}>
 */
function mmm_breadcrumb_items(): array {

	$items = array( array( __( 'Home', 'mixmymeals-hello-child' ), home_url( '/' ) ) );

	if ( is_404() ) {
		$items[] = array( __( '404 Not found', 'mixmymeals-hello-child' ), '' );
		return $items;
	}

	if ( is_home() ) {
		$blog    = (int) get_option( 'page_for_posts' );
		$items[] = array( $blog ? get_the_title( $blog ) : __( 'Blog', 'mixmymeals-hello-child' ), '' );
		return $items;
	}

	if ( is_post_type_archive() ) {
		$items[] = array( (string) post_type_archive_title( '', false ), '' );
		return $items;
	}

	if ( is_category() || is_tag() || is_tax() ) {

		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {

			$taxonomy = get_taxonomy( $term->taxonomy );
			$type     = $taxonomy && ! empty( $taxonomy->object_type ) ? (string) $taxonomy->object_type[0] : '';

			if ( '' !== $type && 'post' !== $type && get_post_type_archive_link( $type ) ) {
				$object  = get_post_type_object( $type );
				$items[] = array( $object ? $object->labels->name : $type, (string) get_post_type_archive_link( $type ) );
			}

			foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $ancestor ) {
				$parent = get_term( $ancestor, $term->taxonomy );
				if ( $parent instanceof WP_Term ) {
					$items[] = array( $parent->name, (string) get_term_link( $parent ) );
				}
			}

			$items[] = array( $term->name, '' );
		}

		return $items;
	}

	if ( is_archive() ) {
		$items[] = array( wp_strip_all_tags( (string) get_the_archive_title() ), '' );
		return $items;
	}

	if ( is_singular() ) {

		$post = get_queried_object();

		if ( $post instanceof WP_Post ) {

			if ( 'post' === $post->post_type ) {
				$categories = get_the_category( $post->ID );
				if ( $categories ) {
					$items[] = array( $categories[0]->name, (string) get_category_link( $categories[0] ) );
				}
			} elseif ( 'page' === $post->post_type ) {
				foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor ) {
					$items[] = array( get_the_title( $ancestor ), (string) get_permalink( $ancestor ) );
				}
			} elseif ( get_post_type_archive_link( $post->post_type ) ) {
				$object  = get_post_type_object( $post->post_type );
				$items[] = array( $object ? $object->labels->name : $post->post_type, (string) get_post_type_archive_link( $post->post_type ) );
			}

			$items[] = array( get_the_title( $post ), '' );
		}
	}

	return $items;
}

/**
 * Brotkrumen-Leiste unter dem Header. Nicht auf der Startseite und nicht in
 * der Suche (wie bisher).
 */
function mmm_breadcrumbs(): void {

	if ( is_front_page() || is_search() ) {
		return;
	}

	$items = (array) apply_filters( 'mmm_breadcrumb_items', mmm_breadcrumb_items() );

	if ( count( $items ) < 2 ) {
		return;
	}

	$last = count( $items ) - 1;

	echo '<div class="mmm-breadcrumbs"><div class="mmm-l-container">'
		. '<nav class="mmm-breadcrumbs__nav" aria-label="' . esc_attr__( 'Breadcrumb', 'mixmymeals-hello-child' ) . '">'
		. '<ul class="mmm-breadcrumbs__list">';

	foreach ( $items as $index => $item ) {

		$label = esc_html( (string) $item[0] );

		if ( $index === $last || '' === (string) $item[1] ) {
			echo '<li class="mmm-breadcrumbs__item"><span' . ( $index === $last ? ' aria-current="page"' : '' ) . '>' . $label . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		} else {
			echo '<li class="mmm-breadcrumbs__item"><a href="' . esc_url( (string) $item[1] ) . '">' . $label . '</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		}
	}

	echo '</ul></nav></div></div>';
}

/**
 * Kopf eines Archivs: Titel, dann die Blöcke, die sich an die Hooks hängen
 * (Beschreibung, Suche, Filter).
 */
function mmm_archive_header(): void {

	if ( is_post_type_archive() ) {
		$title = (string) post_type_archive_title( '', false );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$title = (string) single_term_title( '', false );
	} else {
		$title = wp_strip_all_tags( (string) get_the_archive_title() );
	}

	$description = (string) get_the_archive_description();
	?>
	<section class="mmm-l-archive-description">
		<h1 class="page-title mmm-l-archive-title"><?php echo esc_html( $title ); ?></h1>
		<?php do_action( 'mmm_after_archive_title' ); ?>
		<?php echo wp_kses_post( wpautop( $description ) ); ?>
		<?php do_action( 'mmm_after_archive_description' ); ?>
	</section>
	<?php
}

/**
 * Die Liste eines Archivs: eine Karte je Beitrag (template-parts/card.php).
 * Ist sie leer, gibt mmm_content_none den Ersatz aus.
 */
function mmm_content_loop(): void {
	?>
	<main id="main" class="site-main">
		<?php if ( have_posts() ) : ?>
			<div class="mmm-l-row">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/card' );
				}
				?>
			</div>
		<?php elseif ( has_action( 'mmm_content_none' ) ) : ?>
			<?php do_action( 'mmm_content_none' ); ?>
		<?php else : ?>
			<div class="mmm-l-row">
				<section class="no-results not-found">
					<p><?php echo esc_html( mmm_content_none_message() ); ?></p>
				</section>
			</div>
		<?php endif; ?>
	</main>
	<?php
}

/**
 * Was eine leere Liste sagt.
 *
 * @return string
 */
function mmm_content_none_message(): string {

	if ( is_tax( array( 'mmm_recipe_category', 'mmm_recipe_tag' ) ) ) {
		return __( 'No recipes found in this category.', 'mixmymeals-hello-child' );
	}

	return __( 'Nothing found here.', 'mixmymeals-hello-child' );
}

/**
 * Eine gewöhnliche Seite: Titel und Inhalt.
 */
function mmm_content_page_loop(): void {
	?>
	<main id="main" class="site-main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'mmm-l-article-single' ); ?> id="post-<?php the_ID(); ?>" itemtype="https://schema.org/CreativeWork" itemscope="itemscope">
				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title" itemprop="headline">', '</h1>' ); ?>
				</header>
				<div class="entry-content clear" itemprop="text">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before'      => '<div class="page-links">' . esc_html__( 'Pages:', 'mixmymeals-hello-child' ),
							'after'       => '</div>',
							'link_before' => '<span class="page-link">',
							'link_after'  => '</span>',
						)
					);
					?>
				</div>
			</article>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</main>
	<?php
}

/**
 * Blätterlinks unter einer Liste.
 */
function mmm_pagination(): void {

	$links = get_the_posts_pagination(
		array(
			'prev_text' => '<span class="mmm-l-left-arrow" aria-hidden="true">&larr;</span> ' . esc_html__( 'Previous', 'mixmymeals-hello-child' ),
			'next_text' => esc_html__( 'Next', 'mixmymeals-hello-child' ) . ' <span class="mmm-l-right-arrow" aria-hidden="true">&rarr;</span>',
		)
	);

	if ( '' === trim( (string) $links ) ) {
		return;
	}

	echo '<div class="mmm-l-pagination">' . $links . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core markup.
}

/**
 * Länge der Listen: 20 Einträge je Seite, wie bisher (die Einstellung lag
 * im Customizer von Astra). Daran hängen die Adressen der Folgeseiten
 * (/rezepte/page/9/), die sich nicht ändern dürfen. Die Suche hat ihre
 * eigene Darstellung und bleibt bei der WordPress-Einstellung.
 *
 * @param WP_Query $query Abfrage.
 */
function mmm_layout_posts_per_page( $query ): void {

	if ( is_admin() || ! $query->is_main_query() || $query->is_search() ) {
		return;
	}

	if ( $query->is_home() || $query->is_archive() ) {
		$query->set( 'posts_per_page', (int) apply_filters( 'mmm_archive_posts_per_page', 20 ) );
	}
}
add_action( 'pre_get_posts', 'mmm_layout_posts_per_page', 5 );
