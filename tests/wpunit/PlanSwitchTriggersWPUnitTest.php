<?php

namespace NewfoldLabs\WP\Module\NextSteps;

/**
 * Test PlanSwitchTriggers class
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\PlanSwitchTriggers
 */
class PlanSwitchTriggersWPUnitTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Clean up any existing plan
		delete_option( 'nfd_next_steps' );
		delete_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION );
	}

	/**
	 * Tear down after each test
	 */
	public function tearDown(): void {
		delete_option( 'nfd_next_steps' );
		delete_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION );
		parent::tearDown();
	}

	// ========================================
	// # Site Type Change Tests
	// ========================================
	// Note: Basic site type change tests are in PlanFactoryWPUnitTest
	// These tests focus on PlanSwitchTriggers-specific functionality

	/**
	 * Test site type change with missing site_type key
	 *
	 * @covers ::on_sitetype_change
	 */
	public function test_on_sitetype_change_missing_key() {
		// Create initial blog plan
		$blog_plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $blog_plan );

		// Simulate site type change with missing key
		$old_value = array( 'other_key' => 'value' );
		$new_value = array( 'other_key' => 'value2' );

		PlanSwitchTriggers::on_sitetype_change( $old_value, $new_value );

		// Verify plan is still blog (no change without site_type key)
		$updated_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'blog', $updated_plan->type );
	}

	// ========================================
	// # WooCommerce Activation Tests
	// ========================================
	// Note: Basic WooCommerce activation tests are in PlanFactoryWPUnitTest
	// These tests focus on PlanSwitchTriggers-specific edge cases

	/**
	 * Test WooCommerce activation does nothing if already ecommerce
	 *
	 * @covers ::on_woocommerce_activation
	 */
	public function test_on_woocommerce_activation_already_ecommerce() {
		// Create initial ecommerce plan
		$ecommerce_plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $ecommerce_plan );

		// Get plan version before
		$plan_before    = PlanRepository::get_current_plan();
		$version_before = $plan_before->version;

		// Simulate WooCommerce activation
		PlanSwitchTriggers::on_woocommerce_activation( 'woocommerce/woocommerce.php', false );

		// Verify plan is still ecommerce
		$updated_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'ecommerce', $updated_plan->type );
	}

	// ========================================
	// # Language Change Tests
	// ========================================

	/**
	 * Test language change triggers resync
	 *
	 * @covers ::on_language_change
	 */
	public function test_on_language_change_triggers_resync() {
		// Create initial plan
		$plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $plan );

		// Mark a task as complete
		$plan->update_task_status( 'blog_build_track', 'create_content', 'blog_first_post', 'done' );
		PlanRepository::save_plan( $plan );

		// Verify task is complete
		$current_plan = PlanRepository::get_current_plan();
		$task = $current_plan->get_task( 'blog_build_track', 'create_content', 'blog_first_post' );
		$this->assertTrue( $task->is_completed() );

		// Simulate language change
		PlanSwitchTriggers::on_language_change( 'en_US', 'es_ES' );

		// Verify plan was resynced (new plan loaded with fresh data)
		$updated_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'blog', $updated_plan->type );
		
		// Task completion should be preserved through merge
		$updated_task = $updated_plan->get_task( 'blog_build_track', 'create_content', 'blog_first_post' );
		$this->assertTrue( $updated_task->is_completed() );
	}

	/**
	 * Test locale switch triggers resync
	 *
	 * @covers ::on_locale_switch
	 */
	public function test_on_locale_switch_triggers_resync() {
		// Create initial plan
		$plan = PlanFactory::create_plan( 'corporate' );
		PlanRepository::save_plan( $plan );

		// Verify we have a corporate plan
		$current_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'corporate', $current_plan->type );

		// Simulate locale switch
		PlanSwitchTriggers::on_locale_switch( 'fr_FR' );

		// Verify plan was resynced
		$updated_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'corporate', $updated_plan->type );
	}

	// ========================================
	// # Integration Tests
	// ========================================

	/**
	 * Test plan switch mechanism works correctly
	 *
	 * @covers ::on_sitetype_change
	 */
	public function test_plan_switch_mechanism() {
		// Create initial blog plan
		$blog_plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $blog_plan );

		// Verify we have a blog plan
		$current_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'blog', $current_plan->type );

		// Use PlanRepository::switch_plan to switch to ecommerce
		PlanRepository::switch_plan( 'ecommerce' );

		// Verify new plan is ecommerce
		$ecommerce_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'ecommerce', $ecommerce_plan->type );

		// Switch back to blog
		PlanRepository::switch_plan( 'blog' );

		// Verify we're back to blog
		$restored_blog_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'blog', $restored_blog_plan->type );
	}

}
