<?php

namespace NewfoldLabs\WP\Module\NextSteps\Tests\WPUnit;

use NewfoldLabs\WP\Module\NextSteps\RedirectHelper;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;

/**
 * Test RedirectHelper functionality (both plugin and template redirects)
 */
class RedirectHelperWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Test that RedirectHelper template methods are accessible
	 */
	public function test_template_methods_accessible() {
		// Test that the methods exist and are callable
		$this->assertTrue( method_exists( RedirectHelper::class, 'get_template_part_editor_url' ) );
		$this->assertTrue( method_exists( RedirectHelper::class, 'get_template_editor_url' ) );
		$this->assertTrue( method_exists( RedirectHelper::class, 'is_block_theme' ) );
		$this->assertTrue( is_callable( array( RedirectHelper::class, 'get_template_part_editor_url' ) ) );
		$this->assertTrue( is_callable( array( RedirectHelper::class, 'get_template_editor_url' ) ) );
		$this->assertTrue( is_callable( array( RedirectHelper::class, 'is_block_theme' ) ) );
	}

	/**
	 * Test block theme detection
	 */
	public function test_block_theme_detection() {
		// Test that the method returns a boolean
		$is_block_theme = RedirectHelper::is_block_theme();
		$this->assertIsBool( $is_block_theme );
	}

	/**
	 * Test that custom plans can use RedirectHelper template methods
	 */
	public function test_custom_plan_can_use_template_url_helper() {
		// Get the template URL (may be null if not a block theme)
		$template_url = RedirectHelper::get_template_part_editor_url( 'header' );

		// Create a custom plan that uses RedirectHelper
		$custom_plan_data = array(
			'id'          => 'custom_test_plan',
			'type'        => 'custom',
			'label'       => 'Custom Test Plan',
			'description' => 'A custom plan that uses RedirectHelper',
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
									'href'     => $template_url ? $template_url : 'fallback-url',
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
	 * Test that RedirectHelper template methods return expected URL structure
	 */
	public function test_template_url_helper_returns_expected_urls() {
		// Only test URL structure if we're in a block theme
		if ( ! RedirectHelper::is_block_theme() ) {
			$this->markTestSkipped( 'Skipping URL structure test - not a block theme' );
			return;
		}

		// Test header template editor URL
		$header_url = RedirectHelper::get_template_part_editor_url( 'header' );
		if ( $header_url ) {
			$this->assertStringContainsString( 'site-editor.php', $header_url );
			$this->assertStringContainsString( 'postType=wp_template_part', $header_url );
			$this->assertStringContainsString( 'canvas=edit', $header_url );
		}

		// Test home template editor URL
		$home_url = RedirectHelper::get_template_editor_url( 'home' );
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
			$is_block_theme = RedirectHelper::is_block_theme();

			if ( ! $is_block_theme ) {
				// If we're not in a block theme, the methods should return null
				$header_url = RedirectHelper::get_template_part_editor_url( 'header' );
				$home_url   = RedirectHelper::get_template_editor_url( 'home' );

				$this->assertNull( $header_url );
				$this->assertNull( $home_url );
			}
		}
	}

	/**
	 * Test that RedirectHelper plugin redirect methods are accessible
	 */
	public function test_plugin_redirect_methods_accessible() {
		// Test that the init method exists
		$this->assertTrue( method_exists( RedirectHelper::class, 'init' ) );
		$this->assertTrue( is_callable( array( RedirectHelper::class, 'init' ) ) );
		
		// Test that the redirect check method exists
		$this->assertTrue( method_exists( RedirectHelper::class, 'check_redirect' ) );
		$this->assertTrue( is_callable( array( RedirectHelper::class, 'check_redirect' ) ) );
	}

	/**
	 * Test that RedirectHelper can be initialized without errors
	 */
	public function test_redirect_helper_initialization() {
		// This should not throw any errors
		$this->assertNull( RedirectHelper::init() );
	}
}
