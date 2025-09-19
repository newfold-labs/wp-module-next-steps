<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;

/**
 * WPUnit tests for Plan DTO
 */
class PlanDTOWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Before test
	 */
	protected function setUp(): void {
		parent::setUp();
		// Load the module bootstrap
		require_once dirname( dirname( __DIR__ ) ) . '/bootstrap.php';
	}

	/**
	 * After test
	 */
	protected function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * Test plan creation
	 */
	public function testPlanCreation() {
		$plan_data = array(
			'id'          => 'test_plan',
			'type'        => 'custom',
			'label'       => 'Test Plan',
			'description' => 'A test plan',
			'version'     => '1.0.0',
			'tracks'      => array(),
		);

		$plan = new Plan( $plan_data );

		$this->assertEquals( 'test_plan', $plan->id );
		$this->assertEquals( 'custom', $plan->type );
		$this->assertEquals( 'Test Plan', $plan->label );
		$this->assertEquals( '1.0.0', $plan->version );
	}

	/**
	 * Test version comparison
	 */
	public function testVersionComparison() {
		$plan_data = array(
			'id'          => 'test_plan',
			'type'        => 'custom',
			'label'       => 'Test Plan',
			'description' => 'A test plan',
			'version'     => '0.9.0',
			'tracks'      => array(),
		);

		$plan = new Plan( $plan_data );

		// Should be outdated since current version is 1.2.0
		$this->assertTrue( $plan->is_version_outdated() );
	}

	/**
	 * Test current version not outdated
	 */
	public function testCurrentVersionNotOutdated() {
		$plan_data = array(
			'id'          => 'test_plan',
			'type'        => 'custom',
			'label'       => 'Test Plan',
			'description' => 'A test plan',
			'version'     => '1.2.0',
			'tracks'      => array(),
		);

		$plan = new Plan( $plan_data );

		// Should not be outdated since version matches current
		$this->assertFalse( $plan->is_version_outdated() );
	}
}
