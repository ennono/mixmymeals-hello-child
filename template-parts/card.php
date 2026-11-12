<?php
/**
 * Eine Karte in der Liste eines Archivs (Rezepte, Essenspläne, …).
 *
 * Das Gerüst ist für alle Inhaltstypen gleich; was eine Karte zeigt, hängen
 * functions.php und die includes an die Hooks: Wochentage, Bewertung,
 * Meta-Zeile, „Einplanen“.
 *
 * @package MixMyMeals Hello Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Essenspläne zeigen statt eines Beitragsbilds die Bilder ihrer Rezepte.
$mmm_card_thumb = 'mmm_meal_plan' !== get_post_type() && has_post_thumbnail();
?>
<article <?php post_class( 'mmm-l-grid-common-col mmm-l-full-width mmm-l-article-post' ); ?> id="post-<?php the_ID(); ?>" itemtype="https://schema.org/CreativeWork" itemscope="itemscope">
	<div class="mmm-l-post-format- <?php echo $mmm_card_thumb ? '' : 'mmm-l-no-thumb '; ?>blog-layout-5 mmm-l-article-inner">

		<?php if ( $mmm_card_thumb ) : ?>
			<div class="mmm-l-blog-featured-section post-thumb mmm-l-width-md-6"><div class="post-thumb-img-content post-thumb"><a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>"><?php the_post_thumbnail( 'large', array( 'itemprop' => 'image' ) ); ?></a></div></div>
		<?php endif; ?>

		<div class="post-content mmm-l-width-md-6">

			<?php do_action( 'mmm_card_title_before' ); ?>
			<h2 class="entry-title mmm-l-blog-single-element" itemprop="headline"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
			<?php do_action( 'mmm_card_title_after' ); ?>

			<header class="entry-header mmm-l-blog-single-element mmm-l-blog-meta-container"></header>
			<?php do_action( 'mmm_card_meta_after' ); ?>

			<div class="mmm-l-excerpt-container mmm-l-blog-single-element">
				<?php the_excerpt(); ?>
			</div>

			<div class="entry-content clear" itemprop="text">
				<?php do_action( 'mmm_card_content_after' ); ?>
			</div>
		</div>
	</div>
</article>
