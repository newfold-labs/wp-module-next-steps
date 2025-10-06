<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\PlanFactory;
use NewfoldLabs\WP\Module\NextSteps\PlanRepository;
use NewfoldLabs\WP\Module\NextSteps\PlanSwitchTriggers;
use NewfoldLabs\WP\Module\NextSteps\StepsApi;
use NewfoldLabs\WP\Module\NextSteps\Tests\WPUnit\TestPlanFactory;

/**
 * WordPress Unit Tests for PlanFactory
 *
 * These tests run in a real WordPress environment with database access.
 * They test the actual integration with WordPress functions and database.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\PlanFactory
 */
class PlanFactoryWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

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
	 * Test load_default_steps when no steps exist
	 */
	public function test_load_default_steps_when_no_steps_exist() {
		// Ensure no steps option exists
		delete_option( PlanRepository::OPTION );

		// Set admin context for the test since load_default_steps requires admin context
		set_current_screen( 'admin' );

		PlanFactory::load_default_steps();

		// Verify that steps were loaded and saved to the module's own option
		$steps_data = get_option( PlanRepository::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertEquals( 'blog_setup', $steps_data['id'] );
		$this->assertEquals( 'blog', $steps_data['type'] );
	}

	/**
	 * Test on_sitetype_change with no change
	 */
	public function test_on_sitetype_change_no_change() {
		// Clean slate
		delete_option( PlanRepository::OPTION );

		$old_value = array( 'site_type' => 'personal' );
		$new_value = array( 'site_type' => 'personal' );

		// Should not trigger any changes
		PlanSwitchTriggers::on_sitetype_change( $old_value, $new_value );

		// Verify no plan was created
		$plan_data = get_option( PlanRepository::OPTION );
		$this->assertFalse( $plan_data );
	}

	/**
	 * Test on_sitetype_change with invalid new value
	 */
	public function test_on_sitetype_change_invalid_new_value() {
		// Clean slate
		delete_option( PlanRepository::OPTION );

		$old_value = array( 'site_type' => 'personal' );
		$new_value = array( 'site_type' => 'invalid_type' );

		// Should not trigger any changes for invalid type
		PlanSwitchTriggers::on_sitetype_change( $old_value, $new_value );

		// Verify no plan was created
		$plan_data = get_option( PlanRepository::OPTION );
		$this->assertFalse( $plan_data );
	}

	/**
	 * Test on_sitetype_change with invalid site type
	 */
	public function test_on_sitetype_change_invalid_site_type() {
		// Clean slate
		delete_option( PlanRepository::OPTION );

		$old_value = array( 'site_type' => 'personal' );
		$new_value = array( 'invalid_key' => 'ecommerce' );

		// Should not trigger any changes for invalid key
		PlanSwitchTriggers::on_sitetype_change( $old_value, $new_value );

		// Verify no plan was created
		$plan_data = get_option( PlanRepository::OPTION );
		$this->assertFalse( $plan_data );
	}

	/**
	 * Test on_sitetype_change creates correct plan types
	 */
	public function test_on_sitetype_change_creates_correct_plan_types() {
		$valid_types = array(
			'personal'  => 'blog_setup',
			'business'  => 'corporate_setup',
			'ecommerce' => 'store_setup',
		);

		$old_site_types = array( 'ecommerce', 'personal', 'business' ); // Rotate old values
		$i              = 0;

		foreach ( $valid_types as $site_type => $expected ) {
			// Clean slate for each test
			delete_option( PlanRepository::OPTION );

			// Use a different old value to ensure a change is detected
			$old_value = array( 'site_type' => $old_site_types[ $i ] );
			$new_value = array( 'site_type' => $site_type );
			++$i;

			// Trigger the change
			PlanSwitchTriggers::on_sitetype_change( $old_value, $new_value );

			// Verify the correct plan was created
			$plan_data = get_option( PlanRepository::OPTION );
			$this->assertIsArray( $plan_data );
			$this->assertEquals( $expected, $plan_data['id'] );
		}
	}

	/**
	 * Test on_woocommerce_activation switches from blog to ecommerce
	 */
	public function test_on_woocommerce_activation() {
		// Clean slate
		delete_option( PlanRepository::OPTION );

		// Set up initial blog steps
		$blog_plan = PlanRepository::switch_plan( 'blog' );
		$this->assertNotFalse( $blog_plan );
		StepsApi::set_data( $blog_plan->to_array() );

		// Verify initial state
		$initial_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'blog_setup', $initial_plan->id );

		// Simulate WooCommerce activation
		PlanSwitchTriggers::on_woocommerce_activation( 'woocommerce/woocommerce.php', false );

		// Verify plan switched to ecommerce
		$final_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'store_setup', $final_plan->id );
		$this->assertEquals( 'ecommerce', $final_plan->type );
	}

	/**
	 * Test on_woocommerce_activation ignores other plugins
	 */
	public function test_on_woocommerce_activation_ignores_other_plugins() {
		// Clean slate
		delete_option( PlanRepository::OPTION );

		// Set up initial blog steps
		$blog_plan = PlanRepository::switch_plan( 'blog' );
		$this->assertNotFalse( $blog_plan );
		StepsApi::set_data( $blog_plan->to_array() );

		// Verify initial state
		$initial_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'blog_setup', $initial_plan->id );

		// Simulate activation of non-WooCommerce plugin
		PlanSwitchTriggers::on_woocommerce_activation( 'other-plugin/other-plugin.php', false );

		// Verify plan did NOT switch (non-WooCommerce plugin should not trigger switch)
		$final_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'blog_setup', $final_plan->id );
		$this->assertEquals( 'blog', $final_plan->type );
	}

	/**
	 * Test load_default_steps backfills solution
	 */
	public function test_load_default_steps_backfills_solution() {
		// Clean slate - simulate existing site without onboarding or solution data
		delete_option( PlanRepository::OPTION );

		// Set admin context for the test since load_default_steps requires admin context
		set_current_screen( 'admin' );

		// Test the full flow - this should load default steps based on site detection
		PlanFactory::load_default_steps();

		// Verify that steps were loaded
		$steps_data = get_option( PlanRepository::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertNotEmpty( $steps_data['id'] );
		$this->assertNotEmpty( $steps_data['type'] );
	}

	/**
	 * Test load_default_steps respects existing solution
	 */
	public function test_load_default_steps_respects_existing_solution() {
		// Clean slate
		delete_option( PlanRepository::OPTION );

		// Set admin context for the test since load_default_steps requires admin context
		set_current_screen( 'admin' );

		// Set an existing solution via transient (primary method)
		$existing_solution = array(
			'site_type' => 'ecommerce',
		);
		set_transient( PlanFactory::SOLUTIONS_TRANSIENT, $existing_solution, HOUR_IN_SECONDS );

		// Also set the onboarding site info option as a fallback
		update_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION, $existing_solution );

		// Load default steps
		PlanFactory::load_default_steps();

		// Verify that ecommerce plan was loaded based on existing solution
		$steps_data = get_option( PlanRepository::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertEquals( 'store_setup', $steps_data['id'] );
		$this->assertEquals( 'ecommerce', $steps_data['type'] );
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
