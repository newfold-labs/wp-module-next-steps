<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\Tests\WPUnit\TestPlanFactory;

/**
 * WordPress Unit Tests for Plan DTO Helper Methods
 *
 * Tests the helper methods added to the Plan DTO for checking
 * existence of tracks, sections, and tasks.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\DTOs\Plan
 */
class PlanHelpersWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Test plan instance
	 *
	 * @var Plan
	 */
	private $plan;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Load TestPlanFactory
		require_once dirname( __DIR__ ) . '/wpunit/TestPlanFactory.php';

		// Create a test plan with known structure for testing
		$this->plan = PlanFactory::create_plan( 'blog' );
	}

	/**
	 * Test has_track returns true for existing track
	 */
	public function test_has_track_returns_true_for_existing_track() {
		$this->assertTrue( $this->plan->has_track( 'blog_build_track' ) );
		$this->assertTrue( $this->plan->has_track( 'blog_brand_track' ) );
		$this->assertTrue( $this->plan->has_track( 'blog_grow_track' ) );
	}

	/**
	 * Test has_track returns false for non-existing track
	 */
	public function test_has_track_returns_false_for_non_existing_track() {
		$this->assertFalse( $this->plan->has_track( 'nonexistent_track' ) );
		$this->assertFalse( $this->plan->has_track( 'store_build_track' ) );
	}

	/**
	 * Test has_track with empty string
	 */
	public function test_has_track_with_empty_string() {
		$this->assertFalse( $this->plan->has_track( '' ) );
	}

	/**
	 * Test has_section returns true for existing section (any track)
	 */
	public function test_has_section_returns_true_for_existing_section_any_track() {
		// Test without specifying track - should search all tracks
		$this->assertTrue( $this->plan->has_section( 'basic_blog_setup' ) );
		$this->assertTrue( $this->plan->has_section( 'create_content' ) );
		$this->assertTrue( $this->plan->has_section( 'first_audience_building' ) );
	}

	/**
	 * Test has_section returns true for existing section in specific track
	 */
	public function test_has_section_returns_true_for_existing_section_in_track() {
		// Test with specific track
		$this->assertTrue( $this->plan->has_section( 'basic_blog_setup', 'blog_build_track' ) );
		$this->assertTrue( $this->plan->has_section( 'first_audience_building', 'blog_brand_track' ) );
	}

	/**
	 * Test has_section returns false when section exists but not in specified track
	 */
	public function test_has_section_returns_false_when_section_not_in_specified_track() {
		// Section exists in blog_build_track but not in blog_brand_track
		$this->assertFalse( $this->plan->has_section( 'basic_blog_setup', 'blog_brand_track' ) );
	}

	/**
	 * Test has_section returns false for non-existing section
	 */
	public function test_has_section_returns_false_for_non_existing_section() {
		$this->assertFalse( $this->plan->has_section( 'nonexistent_section' ) );
		$this->assertFalse( $this->plan->has_section( 'setup_products', 'blog_build_track' ) );
	}

	/**
	 * Test has_section with invalid track returns false
	 */
	public function test_has_section_with_invalid_track_returns_false() {
		$this->assertFalse( $this->plan->has_section( 'basic_blog_setup', 'nonexistent_track' ) );
	}

	/**
	 * Test has_task returns true for existing task (any location)
	 */
	public function test_has_task_returns_true_for_existing_task_any_location() {
		// Search everywhere without specifying track or section
		$this->assertTrue( $this->plan->has_task( 'blog_quick_setup' ) );
		$this->assertTrue( $this->plan->has_task( 'blog_first_post' ) );
	}

	/**
	 * Test has_task returns true for existing task in specific section
	 */
	public function test_has_task_returns_true_for_task_in_specific_section() {
		// Search in specific section (any track)
		$this->assertTrue( $this->plan->has_task( 'blog_quick_setup', 'basic_blog_setup' ) );
		$this->assertTrue( $this->plan->has_task( 'blog_first_post', 'create_content' ) );
	}

	/**
	 * Test has_task returns true for existing task in specific track
	 */
	public function test_has_task_returns_true_for_task_in_specific_track() {
		// Search in specific track (any section)
		$this->assertTrue( $this->plan->has_task( 'blog_quick_setup', '', 'blog_build_track' ) );
	}

	/**
	 * Test has_task returns false when task exists but not in specified section
	 */
	public function test_has_task_returns_false_when_task_not_in_specified_section() {
		// Task exists in create_content but not in basic_blog_setup
		$this->assertFalse( $this->plan->has_task( 'blog_first_post', 'basic_blog_setup' ) );
	}

	/**
	 * Test has_task returns false for non-existing task
	 */
	public function test_has_task_returns_false_for_non_existing_task() {
		$this->assertFalse( $this->plan->has_task( 'nonexistent_task' ) );
		$this->assertFalse( $this->plan->has_task( 'store_add_product' ) );
	}

	/**
	 * Test has_task delegates to has_exact_task when all params provided
	 */
	public function test_has_task_delegates_to_has_exact_task_when_all_params_provided() {
		// When all three parameters are provided, should use exact path check
		$this->assertTrue(
			$this->plan->has_task( 'blog_quick_setup', 'basic_blog_setup', 'blog_build_track' )
		);

		// Should return false if any part of path is wrong
		$this->assertFalse(
			$this->plan->has_task( 'blog_quick_setup', 'wrong_section', 'blog_build_track' )
		);
	}

	/**
	 * Test has_exact_task returns true for valid complete path
	 */
	public function test_has_exact_task_returns_true_for_valid_path() {
		$this->assertTrue(
			$this->plan->has_exact_task( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup' )
		);
		$this->assertTrue(
			$this->plan->has_exact_task( 'blog_build_track', 'create_content', 'blog_first_post' )
		);
	}

	/**
	 * Test has_exact_task returns false with invalid track
	 */
	public function test_has_exact_task_returns_false_with_invalid_track() {
		$this->assertFalse(
			$this->plan->has_exact_task( 'nonexistent_track', 'basic_blog_setup', 'blog_quick_setup' )
		);
	}

	/**
	 * Test has_exact_task returns false with invalid section
	 */
	public function test_has_exact_task_returns_false_with_invalid_section() {
		$this->assertFalse(
			$this->plan->has_exact_task( 'blog_build_track', 'nonexistent_section', 'blog_quick_setup' )
		);
	}

	/**
	 * Test has_exact_task returns false with invalid task
	 */
	public function test_has_exact_task_returns_false_with_invalid_task() {
		$this->assertFalse(
			$this->plan->has_exact_task( 'blog_build_track', 'basic_blog_setup', 'nonexistent_task' )
		);
	}

	/**
	 * Test has_exact_task returns false when task is in wrong section
	 */
	public function test_has_exact_task_returns_false_when_task_in_wrong_section() {
		// blog_first_post exists but not in basic_blog_setup
		$this->assertFalse(
			$this->plan->has_exact_task( 'blog_build_track', 'basic_blog_setup', 'blog_first_post' )
		);
	}

	/**
	 * Test helper methods work with store plan
	 */
	public function test_helpers_work_with_store_plan() {
		$store_plan = PlanFactory::create_plan( 'ecommerce' );

		$this->assertTrue( $store_plan->has_track( 'store_build_track' ) );
		$this->assertTrue( $store_plan->has_section( 'setup_products' ) );
		$this->assertTrue( $store_plan->has_task( 'store_add_product' ) );
		$this->assertTrue(
			$store_plan->has_exact_task( 'store_build_track', 'setup_products', 'store_add_product' )
		);
	}

	/**
	 * Test helper methods work with corporate plan
	 */
	public function test_helpers_work_with_corporate_plan() {
		$corporate_plan = PlanFactory::create_plan( 'corporate' );

		$this->assertTrue( $corporate_plan->has_track( 'corporate_build_track' ) );
		$this->assertTrue( $corporate_plan->has_section( 'basic_site_setup' ) );
		$this->assertTrue( $corporate_plan->has_task( 'corporate_quick_setup' ) );
		$this->assertTrue(
			$corporate_plan->has_exact_task( 'corporate_build_track', 'basic_site_setup', 'corporate_quick_setup' )
		);
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		parent::tearDown();
	}
}

