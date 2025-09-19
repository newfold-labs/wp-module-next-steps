<?php

namespace NewfoldLabs\WP\Module\NextSteps;

/**
 * Module Loading tests for Next Steps Module.
 *
 * Tests that verify the Next Steps module and its core classes are properly loaded.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\NextSteps
 */
class ModuleLoadingWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Test that WordPress factory is working
	 */
	public function test_wordpress_factory_works() {
		$post = static::factory()->post->create_and_get();

		$this->assertInstanceOf( \WP_Post::class, $post );
	}

	/**
	 * Test that the Next Steps module class is properly loaded
	 */
	public function test_next_steps_class_loaded() {
		// Test that our module is properly loaded
		$this->assertTrue( class_exists( 'NewfoldLabs\WP\Module\NextSteps\NextSteps' ) );
	}

	/**
	 * Test that the PlanRepository class is properly loaded
	 */
	public function test_plan_repository_class_loaded() {
		// Test that our PlanRepository class exists
		$this->assertTrue( class_exists( 'NewfoldLabs\WP\Module\NextSteps\PlanRepository' ) );
	}
}
