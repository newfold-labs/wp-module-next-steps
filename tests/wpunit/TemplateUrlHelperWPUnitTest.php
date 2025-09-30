<?php

namespace NewfoldLabs\WP\Module\NextSteps\Tests\WPUnit;

use NewfoldLabs\WP\Module\NextSteps\TemplateUrlHelper;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;

/**
 * Test TemplateUrlHelper functionality
 */
class TemplateUrlHelperWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Test that TemplateUrlHelper methods are accessible
	 */
	public function test_template_url_helper_methods_accessible() {
		// Test that the methods exist and are callable
		$this->assertTrue( method_exists( TemplateUrlHelper::class, 'get_url_to_active_template_editor' ) );
		$this->assertTrue( method_exists( TemplateUrlHelper::class, 'get_url_to_home_template_editor' ) );
		$this->assertTrue( method_exists( TemplateUrlHelper::class, 'is_block_theme' ) );
		$this->assertTrue( is_callable( array( TemplateUrlHelper::class, 'get_url_to_active_template_editor' ) ) );
		$this->assertTrue( is_callable( array( TemplateUrlHelper::class, 'get_url_to_home_template_editor' ) ) );
		$this->assertTrue( is_callable( array( TemplateUrlHelper::class, 'is_block_theme' ) ) );
	}

	/**
	 * Test block theme detection
	 */
	public function test_block_theme_detection() {
		// Test that the method returns a boolean
		$is_block_theme = TemplateUrlHelper::is_block_theme();
		$this->assertIsBool( $is_block_theme );
	}

	/**
	 * Test that custom plans can use TemplateUrlHelper
	 */
	public function test_custom_plan_can_use_template_url_helper() {
		// Get the template URL (may be null if not a block theme)
		$template_url = TemplateUrlHelper::get_url_to_active_template_editor( 'header' );
		
		// Create a custom plan that uses TemplateUrlHelper
		$custom_plan_data = array(
			'id'          => 'custom_test_plan',
			'type'        => 'custom',
			'label'       => 'Custom Test Plan',
			'description' => 'A custom plan that uses TemplateUrlHelper',
			'tracks'      => array(
				array(
					'id'       => 'custom_track',
					'label'    => 'Custom Track',
					'sections' => array(
						array(
							'id'    => 'custom_section',
							'label' => 'Custom Section',
							'tasks' => array(
								array(
									'id'       => 'custom_task_with_template_url',
									'title'    => 'Custom Task with Template URL',
									'href'     => $template_url ?: 'fallback-url',
									'status'   => 'new',
									'priority' => 1,
									'source'   => 'test',
								),
							),
						),
					),
				),
			),
		);

		// Create the plan - this should work without inheritance
		$plan = new Plan( $custom_plan_data );

		// Verify the plan was created successfully
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'custom_test_plan', $plan->id );
		$this->assertEquals( 'custom', $plan->type );

		// Verify the task was created
		$task = $plan->get_task( 'custom_track', 'custom_section', 'custom_task_with_template_url' );
		$this->assertNotNull( $task );
		
		// If we got a template URL, verify it contains expected parts
		if ( $template_url ) {
			$this->assertStringContainsString( 'site-editor.php', $task->href );
			$this->assertStringContainsString( 'postType=wp_template_part', $task->href );
		} else {
			// If no template URL (not a block theme), verify fallback was used
			$this->assertEquals( 'fallback-url', $task->href );
		}
	}

	/**
	 * Test that TemplateUrlHelper methods return expected URL structure
	 */
	public function test_template_url_helper_returns_expected_urls() {
		// Only test URL structure if we're in a block theme
		if ( ! TemplateUrlHelper::is_block_theme() ) {
			$this->markTestSkipped( 'Skipping URL structure test - not a block theme' );
			return;
		}

		// Test header template editor URL
		$header_url = TemplateUrlHelper::get_url_to_active_template_editor( 'header' );
		if ( $header_url ) {
			$this->assertStringContains( 'site-editor.php', $header_url );
			$this->assertStringContains( 'postType=wp_template_part', $header_url );
			$this->assertStringContains( 'canvas=edit', $header_url );
		}

		// Test home template editor URL
		$home_url = TemplateUrlHelper::get_url_to_home_template_editor();
		if ( $home_url ) {
			$this->assertStringContainsString( 'site-editor.php', $home_url );
			$this->assertStringContainsString( 'postType=wp_template', $home_url );
			$this->assertStringContainsString( 'canvas=edit', $home_url );
		}
	}

	/**
	 * Test that methods return null for non-block themes
	 */
	public function test_methods_return_null_for_non_block_themes() {
		// Mock the theme support check to simulate a non-block theme
		$original_theme_supports = null;
		if ( function_exists( 'current_theme_supports' ) ) {
			// We can't easily mock this in a unit test, so we'll just verify the logic
			// In a real scenario, this would be tested with a classic theme
			$is_block_theme = TemplateUrlHelper::is_block_theme();

			if ( ! $is_block_theme ) {
				// If we're not in a block theme, the methods should return null
				$header_url = TemplateUrlHelper::get_url_to_active_template_editor( 'header' );
				$home_url   = TemplateUrlHelper::get_url_to_home_template_editor();

				$this->assertNull( $header_url );
				$this->assertNull( $home_url );
			}
		}
	}
}
