<?php

namespace NewfoldLabs\WP\Module\NextSteps;

/**
 *  Example Unit test for Next Steps Module.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\NextSteps
 */
class ExampleWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Test that the module is working
	 */
	public function test_it_works() {
		$post = static::factory()->post->create_and_get();

		$this->assertInstanceOf( \WP_Post::class, $post );
	}

	/**
	 * Test that the Next Steps module is properly loaded
	 */
	public function test_next_steps_module_loaded() {
		// Test that our module is properly loaded
		$this->assertTrue( class_exists( 'NewfoldLabs\WP\Module\NextSteps\NextSteps' ) );
	}

	/**
	 * Test that the PlanRepository class exists
	 */
	public function test_plan_repository_exists() {
		// Test that our PlanRepository class exists
		$this->assertTrue( class_exists( 'NewfoldLabs\WP\Module\NextSteps\PlanRepository' ) );
	}
}
