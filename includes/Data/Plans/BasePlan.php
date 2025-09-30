<?php

namespace NewfoldLabs\WP\Module\NextSteps\Data\Plans;

/**
 * BasePlan - Abstract base class for plan implementations
 *
 * This class provides foundational methods and structure for plan implementations,
 * such as retrieving active template part slugs and generating editor URLs for
 * WordPress block themes. It is intended to be extended by specific plan classes
 * that define concrete steps and logic for various user workflows.
 *
 * Key Features:
 * - Utility methods for working with block templates and template parts
 * - Methods to generate URLs for editing templates in the site editor
 * - Designed for extensibility by plan subclasses
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
