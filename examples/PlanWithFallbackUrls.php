<?php

namespace NewfoldLabs\WP\Module\NextSteps\Examples;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\TemplateUrlHelper;

/**
 * Example of how to create a plan with fallback URLs for classic themes
 *
 * This example demonstrates how to handle cases where template URLs
 * are not available (e.g., when using a classic PHP theme instead of a block theme).
 */
class PlanWithFallbackUrls {

	/**
	 * Get a plan with fallback URLs for classic themes
	 *
	 * @return Plan
	 */
	public static function get_plan(): Plan {
		return new Plan(
			array(
				'id'          => 'example_plan_with_fallbacks',
				'type'        => 'custom',
				'label'       => 'Example Plan with Fallbacks',
				'description' => 'A plan that handles both block and classic themes',
				'tracks'      => array(
					array(
						'id'       => 'customization_track',
						'label'    => 'Customization',
						'sections' => array(
							array(
								'id'    => 'header_customization',
								'label' => 'Header Customization',
								'tasks' => array(
									array(
										'id'       => 'customize_header',
										'title'    => 'Customize Header',
										'href'     => self::get_header_customization_url(),
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'example',
									),
								),
							),
							array(
								'id'    => 'footer_customization',
								'label' => 'Footer Customization',
								'tasks' => array(
									array(
										'id'       => 'customize_footer',
										'title'    => 'Customize Footer',
										'href'     => self::get_footer_customization_url(),
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'example',
									),
								),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Get header customization URL with fallback for classic themes
	 *
	 * @return string
	 */
	private static function get_header_customization_url(): string {
		// Try to get block theme template editor URL
		$template_url = TemplateUrlHelper::get_url_to_active_template_editor( 'header' );
		
		if ( $template_url ) {
			// Block theme - use site editor
			return $template_url;
		}
		
		// Classic theme - fallback to customizer
		return '{siteUrl}/wp-admin/customize.php?autofocus[section]=header_image';
	}

	/**
	 * Get footer customization URL with fallback for classic themes
	 *
	 * @return string
	 */
	private static function get_footer_customization_url(): string {
		// Try to get block theme template editor URL
		$template_url = TemplateUrlHelper::get_url_to_active_template_editor( 'footer' );
		
		if ( $template_url ) {
			// Block theme - use site editor
			return $template_url;
		}
		
		// Classic theme - fallback to widgets or customizer
		return '{siteUrl}/wp-admin/widgets.php';
	}
}
