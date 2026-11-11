<?php
/**
 * Einzelansicht eines Blogbeitrags.
 *
 * Gebaut wie die Einzelansichten der eigenen Inhaltstypen: eigener Hero statt
 * Astras Titelzeile (`mmm_blog_disable_astra_title()` hängt die ab), darunter
 * der Inhalt in Abschnitten mit `mmm-section-head`. Astras Beitragsmeta —
 * Autor, Datum, Kommentarzahl in einer Zeile unter dem Titel — bleibt
 * ebenfalls weg; dieselben Angaben stehen hier als Chips im Hero, im
 * Vokabular der Rezept- und Planseiten.
 *
 * Eingebettete Rezepte und Pläne ([mmm_recipe_embed], [mmm_meal_plan_embed])
 * laufen durch the_content() und bringen ihre eigene Optik mit.
 *
 * @package MixMyMeals Astra Child
 * @since 1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

<div id="primary" <?php astra_primary_class(); ?>>

	<?php
	while ( have_posts() ) :
		the_post();
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'mmm-single-post' ); ?>>

			<div class="mmm-plan-page">

				<header class="mmm-plan-hero mmm-post-hero">
					<h1><?php the_title(); ?></h1>

					<?php echo mmm_blog_meta_chips(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>

					<?php if ( has_excerpt() ) : ?>
						<p class="mmm-plan-hero__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</header>

				<?php astra_primary_content_top(); ?>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="mmm-post-figure">
						<?php the_post_thumbnail( 'large' ); ?>
					</figure>
				<?php endif; ?>

				<div class="entry-content mmm-post-content">
					<?php the_content(); ?>

					<?php
					wp_link_pages(
						array(
							'before' => '<nav class="mmm-post-pages">' . esc_html__( 'Pages:', 'mixmymeals-astra-child' ) . ' ',
							'after'  => '</nav>',
						)
					);
					?>
				</div>

				<?php
				/*
				 * Schlagwörter unter dem Text, Kategorien oben im Hero: die
				 * Kategorie sagt vorher, worum es geht, das Schlagwort führt
				 * hinterher weiter.
				 */
				$mmm_tags = get_the_tags();
				?>
				<?php if ( $mmm_tags && ! is_wp_error( $mmm_tags ) ) : ?>
					<section class="mmm-recipe-section mmm-post-tags">
						<div class="mmm-section-head">
							<h2><?php esc_html_e( 'Tags', 'mixmymeals-astra-child' ); ?></h2>
							<span class="mmm-section-head__line"></span>
						</div>
						<p class="mmm-post-tags__list">
							<?php foreach ( $mmm_tags as $mmm_tag ) : ?>
								<a class="mmm-chip" href="<?php echo esc_url( (string) get_tag_link( $mmm_tag ) ); ?>">
									<?php echo esc_html( $mmm_tag->name ); ?>
								</a>
							<?php endforeach; ?>
						</p>
					</section>
				<?php endif; ?>

				<?php
				/*
				 * Die Autorenbox trägt die Klassen der Rezept-Autorenbox, damit
				 * sie dieselbe Karte ist — nur gefüllt aus dem WordPress-Konto
				 * statt aus der mmm_author-Taxonomie. Ohne Biografie entfällt
				 * sie: ein Kasten, in dem nur ein Name steht, sagt nichts.
				 */
				$mmm_bio = trim( (string) get_the_author_meta( 'description' ) );
				?>
				<?php if ( '' !== $mmm_bio ) : ?>
					<section class="mmm-author-box mmm-post-author">
						<div class="mmm-author-box__media">
							<?php echo get_avatar( get_the_author_meta( 'ID' ), 96, '', '', array( 'class' => 'mmm-author-box__avatar' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<div class="mmm-author-box__body">
							<p class="mmm-author-box__eyebrow"><?php esc_html_e( 'Written by', 'mixmymeals-astra-child' ); ?></p>
							<p class="mmm-author-box__name">
								<a class="mmm-author-box__link" href="<?php echo esc_url( (string) get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ); ?>">
									<?php echo esc_html( (string) get_the_author() ); ?>
								</a>
							</p>
							<p class="mmm-author-box__bio"><?php echo esc_html( $mmm_bio ); ?></p>
						</div>
					</section>
				<?php endif; ?>

				<?php
				$mmm_prev = get_previous_post();
				$mmm_next = get_next_post();
				?>
				<?php if ( $mmm_prev || $mmm_next ) : ?>
					<nav class="mmm-post-nav" aria-label="<?php esc_attr_e( 'More posts', 'mixmymeals-astra-child' ); ?>">
						<?php if ( $mmm_prev instanceof WP_Post ) : ?>
							<a class="mmm-post-nav__link mmm-post-nav__link--prev" href="<?php echo esc_url( (string) get_permalink( $mmm_prev ) ); ?>">
								<span class="mmm-post-nav__label"><?php esc_html_e( 'Previous post', 'mixmymeals-astra-child' ); ?></span>
								<span class="mmm-post-nav__title"><?php echo esc_html( get_the_title( $mmm_prev ) ); ?></span>
							</a>
						<?php endif; ?>

						<?php if ( $mmm_next instanceof WP_Post ) : ?>
							<a class="mmm-post-nav__link mmm-post-nav__link--next" href="<?php echo esc_url( (string) get_permalink( $mmm_next ) ); ?>">
								<span class="mmm-post-nav__label"><?php esc_html_e( 'Next post', 'mixmymeals-astra-child' ); ?></span>
								<span class="mmm-post-nav__title"><?php echo esc_html( get_the_title( $mmm_next ) ); ?></span>
							</a>
						<?php endif; ?>
					</nav>
				<?php endif; ?>

				<?php if ( comments_open() || get_comments_number() ) : ?>
					<section class="mmm-recipe-section mmm-post-comments">
						<?php comments_template(); ?>
					</section>
				<?php endif; ?>

			</div>

		</article>

	<?php endwhile; ?>

	<?php astra_primary_content_bottom(); ?>

</div><!-- #primary -->

<?php get_footer(); ?>
