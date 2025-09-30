<?php

namespace NewfoldLabs\WP\Module\NextSteps\Data\Plans;

/**
 * StorePlan - Defines the structured plan for ecommerce store setup
 *
 * This class provides a comprehensive step-by-step plan specifically designed for
 * ecommerce store owners using WooCommerce. The plan focuses on building a complete
 * online store from initial setup to advanced marketing and performance optimization.
 *
 * Plan Structure:
 * The plan is organized into a single "Build" track with multiple sections covering:
 * - Store customization (logo, colors, fonts, header, footer, homepage)
 * - Product management (adding first product)
 * - Payment setup (configuring payment methods)
 * - Shopping experience (customizing shop, cart, and checkout pages)
 * - Marketing strategy (popups, gift cards, abandoned cart emails, email customization)
 * - Performance optimization (Jetpack Boost integration)
 * - Review collection and display
 * - Affiliate program setup
 * - SEO optimization with Yoast Premium
 *
 * Each task includes:
 * - Unique identifier for tracking completion
 * - Localized title and description
 * - Direct links to WooCommerce admin areas or external resources
 * - Priority ordering for logical progression
 * - Status tracking (new, done, dismissed)
 * - Source attribution for analytics
 * - Data attributes for event tracking and modal interactions
 *
 * The plan is designed to guide users through the complete process of setting up
 * a successful ecommerce store, from initial customization to advanced marketing
 * and performance optimization strategies.
 *
 * @package NewfoldLabs\WP\Module\NextSteps\Data\Plans
 * @since   1.0.0
 *
 * @author  Newfold Labs
 */
abstract class BasePlan {
	/** Get the slug of the header template part used in a given template
	 *
	 * @param string $template_slug The slug of the template (e.g., 'index', 'front-page', 'single').
	 * @param string $tag_name      The HTML tag name to look for (e.g., 'header', 'footer').
	 * @return string|null The slug of the header template part, or null if not found.
	 */
	private static function get_active_template_part_slug( string $tag_name, string $template_slug = 'index' ): ?string {
		$theme    = wp_get_theme()->get_stylesheet();
		$template = get_block_template( "{$theme}//{$template_slug}" );

		if ( ! $template ) {
			return null;
		}

		$blocks = parse_blocks( $template->content );
		$stack  = $blocks;

		while ( $stack ) {
			$block = array_shift( $stack );

			if ( $block['blockName'] === 'core/template-part' ) {
				$slug       = $block['attrs']['slug'] ?? null;
				$part_theme = $block['attrs']['theme'] ?? $theme;

				if ( $slug ) {
					$part = get_block_template( "{$part_theme}//{$slug}", 'wp_template_part' );

					$area_prop = isset( $part->area ) ? $part->area : null;

					$area_tax = null;
					if ( isset( $part->wp_id ) && $part->wp_id ) {
						$terms = wp_get_post_terms( $part->wp_id, 'wp_template_part_area', [ 'fields' => 'slugs' ] );
						if ( ! is_wp_error( $terms ) && $terms ) {
							$area_tax = $terms[0] ?? null; // es. 'header', 'footer'
						}
					}
					$is_tag = ( $block['attrs']['tagName'] ?? '' ) === $tag_name;

					if ( $area_prop === $tag_name || $area_tax === $tag_name || $is_tag ) {
						return $slug;
					}
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$stack = array_merge( $stack, $block['innerBlocks'] );
			}
		}

		return null;
	}

	/**
	 * Get URL to edit the active header or footer template part used in a given template
	 *
	 * @param string $tag_name      The HTML tag name to look for (e.g., 'header', 'footer').
	 * @param string $template_slug The slug of the template (e.g., 'index', 'front-page', 'single').
	 * @return string|null The URL to edit the active template part, or null if not found.
	 */
	protected static function get_url_to_active_template_editor( string $tag_name, string $template_slug = 'index' ): ?string {
		$theme = wp_get_theme()->get_stylesheet();
		$slug  = self::get_active_template_part_slug( $tag_name, $template_slug );

		if ( ! $slug ) {
			return null;
		}

		$url = add_query_arg(
			array(
				'postType' => 'wp_template_part',
				'postId'   => "{$theme}//{$slug}",
				'canvas'   => 'edit',
			),
			'{siteUrl}/wp-admin/site-editor.php'
		);

		return $url;
	}

	/**
	 * Get URL to edit the home template (front-page or index)
	 *
	 * @return string|null The URL to edit the home template, or null if not found.
	 */
	protected static function get_url_to_home_template_editor(): ?string {
		$theme = wp_get_theme()->get_stylesheet();

		$template_slug = 'home';
		$template      = get_block_template( "{$theme}//{$template_slug}" );

		if ( ! $template ) {
			$template_slug = 'index';
		}

		return add_query_arg(
			array(
				'postType' => 'wp_template',
				'postId'   => "{$theme}//{$template_slug}",
				'canvas'   => 'edit',
			),
			'{siteUrl}/wp-admin/site-editor.php'
		);
	}
}
