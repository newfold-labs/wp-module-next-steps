<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\Data\Plans\PlanBrandUrls;

/**
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\Data\Plans\PlanBrandUrls
 */
class PlanBrandUrlsWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * @var callable|null
	 */
	private $filter_callback;

	public function tearDown(): void {
		if ( null !== $this->filter_callback ) {
			remove_filter( 'newfold/next-steps/brand-plugin-id', $this->filter_callback );
			$this->filter_callback = null;
		}
		parent::tearDown();
	}

	/**
	 * @param string $plugin_id Plugin id to simulate.
	 */
	private function set_brand_plugin_id( string $plugin_id ): void {
		$this->filter_callback = function () use ( $plugin_id ) {
			return $plugin_id;
		};
		add_filter( 'newfold/next-steps/brand-plugin-id', $this->filter_callback );
	}

	public function test_bluehost_returns_bluehost_url_for_known_task() {
		$this->set_brand_plugin_id( 'bluehost' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_welcome_subscribe_popup' );

		$this->assertSame(
			'https://www.bluehost.com/blog/improve-conversion-rate-website-pop-ups/',
			$url
		);
	}

	public function test_web_returns_mapped_url_for_known_task() {
		$this->set_brand_plugin_id( 'web' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_welcome_subscribe_popup' );

		$this->assertSame(
			'https://www.networksolutions.com/help/improverate-website-pop-ups/',
			$url
		);
	}

	public function test_web_returns_hash_for_unmapped_task() {
		$this->set_brand_plugin_id( 'web' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_embed_social_feed' );

		$this->assertSame( '#', $url );
	}

	public function test_unknown_plugin_id_falls_back_to_bluehost() {
		$this->set_brand_plugin_id( 'unknown-brand' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_welcome_subscribe_popup' );

		$this->assertSame(
			'https://www.bluehost.com/blog/improve-conversion-rate-website-pop-ups/',
			$url
		);
	}

	public function test_web_security_plugin_uses_brand_admin_page() {
		$this->set_brand_plugin_id( 'web' );

		$url = PlanBrandUrls::resolve_task_link( 'corporate_install_security_plugin' );

		$this->assertSame(
			'{siteUrl}/wp-admin/admin.php?page=web#/marketplace/security',
			$url
		);
	}

	public function test_crazy_domain_returns_hash_for_unmapped_task() {
		$this->set_brand_plugin_id( 'crazy-domain' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_welcome_subscribe_popup' );

		$this->assertSame( '#', $url );
	}
}
