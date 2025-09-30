<?php

namespace NewfoldLabs\WP\Module\NextSteps;

/**
 * Template URL Helper
 *
 * Provides utility methods for generating WordPress admin URLs related to
 * block theme templates and template parts. This class handles the complex
 * logic of finding active template parts and generating appropriate editor URLs.
 *
 * Key Features:
 * - Utility methods for working with block templates and template parts
 * - Methods to generate URLs for editing templates in the site editor
 * - WordPress block theme-specific functionality
 * - Automatic detection of block theme support
 * - Reusable across different plan types and custom plans
 * - Safe fallback for classic PHP themes (returns null)
 *
 * Note: These methods only work with block themes. For classic PHP themes,
 * the methods will return null as the site editor is not available.
 *
 * @package NewfoldLabs\WP\Module\NextSteps
 * @since   1.0.0
 *
 * @author  Newfold Labs
 */
class TemplateUrlHelper {

	/**
	 * Get the slug of the header template part used in a given template
	 *
	 * This method searches through a block template to find template parts that match
	 * the specified tag name (e.g., 'header', 'footer'). It uses multiple detection
	 * methods to ensure compatibility with different theme implementations.
	 *
	 * @param string $tag_name      The HTML tag name to look for (e.g., 'header', 'footer').
	 * @param string $template_slug The slug of the template (e.g., 'index', 'front-page', 'single').
	 * @return string|null The slug of the header template part, or null if not found.
	 */
	private static function get_active_template_part_slug( string $tag_name, string $template_slug = 'index' ): ?string {
		// Return null if WordPress functions aren't available (e.g., during testing)
		if ( ! function_exists( 'wp_get_theme' ) || ! function_exists( 'get_block_template' ) ) {
			return null;
		}

		// Get the current theme's stylesheet name for template lookup
		$theme    = wp_get_theme()->get_stylesheet();
		$template = get_block_template( "{$theme}//{$template_slug}" );

		if ( ! $template ) {
			return null;
		}

		// Parse the template content into blocks for analysis
		$blocks = parse_blocks( $template->content );
		$stack  = $blocks; // Use a stack to traverse nested blocks

		// Traverse all blocks in the template (including nested ones)
		while ( $stack ) {
			$block = array_shift( $stack );

			// Look for template-part blocks specifically
			if ( 'core/template-part' === $block['blockName'] ) {
				// Extract the template part slug and theme from block attributes
				$slug       = $block['attrs']['slug'] ?? null;
				$part_theme = $block['attrs']['theme'] ?? $theme; // Default to current theme

				if ( $slug ) {
					// Get the actual template part object to examine its properties
					$part = get_block_template( "{$part_theme}//{$slug}", 'wp_template_part' );

					// Method 1: Check the 'area' property directly on the template part
					$area_prop = isset( $part->area ) ? $part->area : null;

					// Method 2: Check the 'wp_template_part_area' taxonomy terms
					// This is how WordPress stores template part areas in the database
					$area_tax = null;
					if ( isset( $part->wp_id ) && $part->wp_id ) {
						$terms = wp_get_post_terms(
							$part->wp_id,
							'wp_template_part_area',
							array( 'fields' => 'slugs' )
						);
						if ( ! is_wp_error( $terms ) && $terms ) {
							$area_tax = $terms[0] ?? null; // Get the first (and usually only) area term
						}
					}

					// Method 3: Check the block's tagName attribute (less common but possible)
					$is_tag = ( $block['attrs']['tagName'] ?? '' ) === $tag_name;

					// Match if any of the three methods identify this as the target area
					if ( $area_prop === $tag_name || $area_tax === $tag_name || $is_tag ) {
						return $slug;
					}
				}
			}

			// Add any nested blocks to the stack for continued traversal
			if ( ! empty( $block['innerBlocks'] ) ) {
				$stack = array_merge( $stack, $block['innerBlocks'] );
			}
		}

		return null;
	}

	/**
	 * Check if the current theme supports block templates
	 *
	 * This method uses two different approaches to detect block theme support:
	 * 1. Check if the theme explicitly declares support for 'block-templates'
	 * 2. Use WordPress's built-in wp_is_block_theme() function (available in WP 5.9+)
	 *
	 * @return bool True if the theme supports block templates, false otherwise.
	 */
	public static function is_block_theme(): bool {
		// Return false if WordPress functions aren't available (e.g., during testing)
		if ( ! function_exists( 'current_theme_supports' ) || ! function_exists( 'wp_get_theme' ) ) {
			return false;
		}

		// Method 1: Check if theme explicitly supports block templates
		// This covers themes that add theme support but aren't full block themes
		$theme_supports_block_templates = current_theme_supports( 'block-templates' );

		// Method 2: Check if this is a full block theme using WordPress's detection
		// This function is available in WordPress 5.9+ and detects true block themes
		$is_block_theme = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();

		// Return true if either method indicates block template support
		return $theme_supports_block_templates || $is_block_theme;
	}

	/**
	 * Get URL to edit the active header or footer template part used in a given template
	 *
	 * This method finds the template part used in a specific template and generates
	 * a URL to edit it in the WordPress site editor. It handles the complex logic
	 * of finding the correct template part and formatting the editor URL.
	 *
	 * @param string $tag_name      The HTML tag name to look for (e.g., 'header', 'footer').
	 * @param string $template_slug The slug of the template (e.g., 'index', 'front-page', 'single').
	 * @return string|null The URL to edit the active template part, or null if not found or theme doesn't support block templates.
	 */
	public static function get_url_to_active_template_editor( string $tag_name, string $template_slug = 'index' ): ?string {
		// Early return if the theme doesn't support block templates
		if ( ! self::is_block_theme() ) {
			return null;
		}

		// Get the current theme name for template part lookup
		$theme = wp_get_theme()->get_stylesheet();

		// Find the template part slug used in the specified template
		$slug = self::get_active_template_part_slug( $tag_name, $template_slug );

		if ( ! $slug ) {
			return null;
		}

		// Build the site editor URL with the necessary parameters
		// The postId format is "theme//template-part-slug" for template parts
		$url = add_query_arg(
			array(
				'postType' => 'wp_template_part', // Indicates we're editing a template part
				'postId'   => "{$theme}//{$slug}", // Theme and template part identifier
				'canvas'   => 'edit', // Opens the editor in edit mode
			),
			'{siteUrl}/wp-admin/site-editor.php' // Base site editor URL
		);

		return $url;
	}

	/**
	 * Get URL to edit the home template (front-page or index)
	 *
	 * This method generates a URL to edit the home page template in the site editor.
	 * It first tries to find a 'home' template, and falls back to 'index' if not found.
	 * This handles the WordPress template hierarchy for home page display.
	 *
	 * @return string|null The URL to edit the home template, or null if not found or theme doesn't support block templates.
	 */
	public static function get_url_to_home_template_editor(): ?string {
		// Early return if the theme doesn't support block templates
		if ( ! self::is_block_theme() ) {
			return null;
		}

		// Return null if WordPress functions aren't available (e.g., during testing)
		if ( ! function_exists( 'wp_get_theme' ) || ! function_exists( 'get_block_template' ) ) {
			return null;
		}

		// Get the current theme name for template lookup
		$theme = wp_get_theme()->get_stylesheet();

		// Try to find a 'home' template first (WordPress template hierarchy)
		$template_slug = 'home';
		$template      = get_block_template( "{$theme}//{$template_slug}" );

		// Fall back to 'index' template if 'home' doesn't exist
		// This follows WordPress's template hierarchy for home page display
		if ( ! $template ) {
			$template_slug = 'index';
		}

		// Build the site editor URL for the home template
		// The postId format is "theme//template-slug" for templates
		return add_query_arg(
			array(
				'postType' => 'wp_template', // Indicates we're editing a template (not template part)
				'postId'   => "{$theme}//{$template_slug}", // Theme and template identifier
				'canvas'   => 'edit', // Opens the editor in edit mode
			),
			'{siteUrl}/wp-admin/site-editor.php' // Base site editor URL
		);
	}
}
