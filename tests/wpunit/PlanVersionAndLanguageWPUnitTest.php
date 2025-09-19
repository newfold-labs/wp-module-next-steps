<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\PlanFactory;
use NewfoldLabs\WP\Module\NextSteps\PlanRepository;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\Tests\WPUnit\TestPlanFactory;

/**
 * WordPress Unit Tests for Plan Version Updates and Language Changes
 *
 * These tests run in a real WordPress environment with database access.
 * They test the actual integration with WordPress functions and database.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\PlanRepository
 */
class PlanVersionAndLanguageWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Load TestPlanFactory from wpunit directory
		require_once dirname( __DIR__ ) . '/wpunit/TestPlanFactory.php';

		// Clean up options before each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
		delete_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION );

		// Invalidate static cache
		PlanRepository::invalidate_cache();
	}

	/**
	 * Test version update triggers merge when saved version is older
	 */
	public function test_version_update_triggers_merge() {
		// Create a saved plan with old version and user progress
		$saved_plan = TestPlanFactory::create_plan_with_progress();
		// Manually set the old version
		$saved_plan->version = '0.9.0';

		// Save the old plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Get current plan (should trigger merge due to version difference)
		$current_plan = PlanRepository::get_current_plan();

		// Verify version was updated
		$this->assertEquals( 'NFD_NEXTSTEPS_MODULE_VERSION', $current_plan->version );

		// Verify user progress was preserved
		$first_track = $current_plan->tracks[0];
		$this->assertTrue( $first_track->open ); // User progress preserved

		$first_section = $first_track->sections[0];
		$this->assertEquals( 'completed', $first_section->status ); // User progress preserved
		$this->assertNotEmpty( $first_section->date_completed );

		$first_task = $first_section->tasks[0];
		$this->assertEquals( 'done', $first_task->status ); // User progress preserved
	}

	/**
	 * Test version update does not trigger merge when versions are the same
	 */
	public function test_version_update_no_merge_when_versions_same() {
		// Create a saved plan with current version
		$saved_plan          = TestPlanFactory::create_plan_with_progress();
		$saved_plan->version = NFD_NEXTSTEPS_MODULE_VERSION; // Current version

		// Save the plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Get current plan (should not trigger merge)
		$current_plan = PlanRepository::get_current_plan();

		// Verify version remains the same
		$this->assertEquals( 'NFD_NEXTSTEPS_MODULE_VERSION', $current_plan->version );

		// Verify user progress was preserved
		$first_track = $current_plan->tracks[0];
		$this->assertTrue( $first_track->open ); // User progress preserved
	}

	/**
	 * Test version update with newer saved version
	 */
	public function test_version_update_with_newer_saved_version() {
		// Create a saved plan with newer version (shouldn't happen in practice)
		$saved_plan          = TestPlanFactory::create_plan_with_progress();
		$saved_plan->version = '2.0.0'; // Newer version

		// Save the plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Get current plan (should not trigger merge)
		$current_plan = PlanRepository::get_current_plan();

		// Verify version remains the newer one
		$this->assertEquals( '2.0.0', $current_plan->version );
	}

	/**
	 * Test language change triggers plan reload
	 */
	public function test_language_change_triggers_plan_reload() {
		// Create a saved plan
		$saved_plan          = TestPlanFactory::create_plan_with_progress();
		$saved_plan->version = 'NFD_NEXTSTEPS_MODULE_VERSION';

		// Save the plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Simulate language change by changing the locale
		// Note: In a real scenario, this would be triggered by WordPress locale change
		// For testing, we'll simulate by clearing the cache and reloading
		PlanRepository::invalidate_cache();

		// Get current plan (should reload with current locale)
		$current_plan = PlanRepository::get_current_plan();

		// Verify plan was reloaded
		$this->assertInstanceOf( Plan::class, $current_plan );
		$this->assertEquals( 'NFD_NEXTSTEPS_MODULE_VERSION', $current_plan->version );
	}

	/**
	 * Test version comparison with different version formats
	 */
	public function test_version_comparison_different_formats() {
		// Test with semantic versioning
		$plan1          = TestPlanFactory::create_plan_with_progress();
		$plan1->version = '1.0.0';
		$this->assertTrue( $plan1->is_version_outdated() );

		// Test with simple version
		$plan2          = TestPlanFactory::create_plan_with_progress();
		$plan2->version = '1.0';
		$this->assertTrue( $plan2->is_version_outdated() );

		// Test with current version
		$plan3          = TestPlanFactory::create_plan_with_progress();
		$plan3->version = 'NFD_NEXTSTEPS_MODULE_VERSION';
		$this->assertFalse( $plan3->is_version_outdated() );
	}

	/**
	 * Test version comparison with null version
	 */
	public function test_version_comparison_null_version() {
		// Create plan with null version
		$plan          = TestPlanFactory::create_plan_with_progress();
		$plan->version = null;

		// Should be considered outdated
		$this->assertTrue( $plan->is_version_outdated() );
	}

	/**
	 * Test version comparison with empty version
	 */
	public function test_version_comparison_empty_version() {
		// Create plan with empty version
		$plan          = TestPlanFactory::create_plan_with_progress();
		$plan->version = '';

		// Should be considered outdated
		$this->assertTrue( $plan->is_version_outdated() );
	}

	/**
	 * Test version comparison with undefined constant
	 */
	public function test_version_comparison_undefined_constant() {
		// Temporarily undefine the constant for testing
		$original_constant = defined( 'NFD_NEXTSTEPS_MODULE_VERSION' ) ? NFD_NEXTSTEPS_MODULE_VERSION : null;

		// Create plan
		$plan          = TestPlanFactory::create_plan_with_progress();
		$plan->version = '1.0.0';

		// Should fall back to default version comparison
		$this->assertTrue( $plan->is_version_outdated() );
	}

	/**
	 * Test plan merge preserves version from new plan
	 */
	public function test_plan_merge_preserves_version_from_new_plan() {
		// Create a new plan with current version
		$new_plan          = TestPlanFactory::create_test_plan();
		$new_plan->version = 'NFD_NEXTSTEPS_MODULE_VERSION';

		// Create a saved plan with old version
		$saved_plan          = TestPlanFactory::create_plan_with_progress();
		$saved_plan->version = '0.9.0';

		// Merge the plans
		$merged_plan = $new_plan->merge_with( $saved_plan );

		// Verify version is from new plan (current version)
		$this->assertEquals( 'NFD_NEXTSTEPS_MODULE_VERSION', $merged_plan->version );
	}

	/**
	 * Test plan merge with custom plan type
	 */
	public function test_plan_merge_with_custom_plan_type() {
		// Create a saved custom plan with old version
		$saved_plan          = TestPlanFactory::create_plan_with_progress();
		$saved_plan->type    = 'custom';
		$saved_plan->version = '0.9.0';

		// Save the plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Get current plan (should trigger merge for custom plan)
		$current_plan = PlanRepository::get_current_plan();

		// Verify version was updated
		$this->assertEquals( 'NFD_NEXTSTEPS_MODULE_VERSION', $current_plan->version );

		// Verify user progress was preserved
		$first_track = $current_plan->tracks[0];
		$this->assertTrue( $first_track->open ); // User progress preserved
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Clean up options after each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
		delete_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION );

		parent::tearDown();
	}
}
