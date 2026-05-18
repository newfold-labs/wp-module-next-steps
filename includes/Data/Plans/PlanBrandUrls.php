<?php
error_log('PlanBrandUrls FILE included');
namespace NewfoldLabs\WP\Module\NextSteps\Data\Plans;

use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * Resolves external / brand-specific task URLs by host plugin id (`container()->plugin()->id`).
 *
 * Blog and corporate task ids are unique (`blog_*`, `corporate_*`). Fallback URLs preserve
 * current Bluehost destinations; other plugin ids listed below use placeholders until replaced.
 */
class PlanBrandUrls {

	/**
	 * Plugin ids that use placeholder URLs (replace per brand in this file or extend mapping).
	 */
	private const PLACEHOLDER_PLUGIN_IDS = array(
		'web',
		'crazy-domain',
		'vodien',
	);

	public static function init(): void {
        error_log('PlanBrandUrls: class loaded and init fired');
    }
	
	/**
	 * Canonical (Bluehost) URLs used when the plugin id does not supply overrides.
	 *
	 * Keys must match task `id` in BlogPlan / CorporatePlan.
	 */
	private const FALLBACK_URLS = array(
		// Blog plan — external help links.
		'blog_welcome_subscribe_popup'            => 'https://www.bluehost.com/blog/improve-conversion-rate-website-pop-ups/',
		'blog_embed_social_feed'                  => 'https://www.bluehost.com/blog/how-to-incorporate-a-social-media-marketing-strategy-with-your-wordpress-website/',
		'blog_submit_search_console'             => 'https://www.bluehost.com/blog/how-to-submit-your-website-to-search-engines/',
		'blog_generate_sitemap'                   => 'https://www.bluehost.com/blog/what-is-a-sitemap-how-it-helps-seo-and-navigation/',
		'blog_display_testimonials'               => 'https://www.bluehost.com/blog/customer-testimonials/',
		'blog_build_newsletter'                   => 'https://www.bluehost.com/blog/how-to-create-an-email-newsletter/',
		'blog_draft_outreach_list'               => 'https://www.bluehost.com/blog/guest-blogging/',
		'blog_run_first_ad'                       => 'https://www.bluehost.com/blog/social-media-advertising-tips/',
		'blog_track_utm_campaigns'                => 'https://www.bluehost.com/blog/how-to-create-a-content-calendar/',
		'blog_plan_content_series'                => 'https://www.bluehost.com/blog/how-to-create-a-content-calendar/',
		'blog_implement_internal_linking'         => 'https://www.bluehost.com/blog/internal-linking-guide/',
		'blog_create_staging_site'                => 'https://www.bluehost.com/blog/what-is-a-staging-site-and-how-to-create-a-bluehost-staging-site-for-your-wordpress-website/',
		// Corporate plan — external / account links.
		'corporate_setup_custom_domain'           => 'https://www.bluehost.com/my-account/domain-center-update/list',
		'corporate_connect_google_business'       => 'https://www.bluehost.com/blog/transfer-google-business-profile-free-website-bluehost/',
		'corporate_create_branded_email'          => 'https://www.bluehost.com/blog/how-to-create-a-business-email-for-free/',
		'corporate_connect_search_console'        => 'https://www.bluehost.com/blog/how-to-submit-your-website-to-search-engines/',
		'corporate_add_contact_form'              => 'https://www.bluehost.com/blog/create-contact-form-wordpress-guide/',
		'corporate_embed_map'                     => 'https://www.bluehost.com/blog/top-wordpress-store-locator-plugins/',
		'corporate_optimize_key_pages'            => 'https://www.bluehost.com/blog/content-optimization-guide/',
		'corporate_generate_submit_sitemap'       => 'https://www.bluehost.com/blog/what-is-a-sitemap-how-it-helps-seo-and-navigation/',
		'corporate_setup_email_capture'           => 'https://www.bluehost.com/blog/how-to-add-an-email-opt-in-form-to-your-website/',
		'corporate_connect_crm'                   => 'https://www.bluehost.com/blog/marketing-automation-tools/',
		'corporate_add_cta_section'               => 'https://www.bluehost.com/blog/call-to-action-tips/',
		'corporate_install_security_plugin'       => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace/security',
		'corporate_run_speed_test'                => 'https://www.bluehost.com/blog/what-is-my-page-speed/',
		'corporate_plan_next_content'             => 'https://www.bluehost.com/blog/how-to-create-a-content-calendar/',
	);

	/**
	 * Resolve href for a task id based on current host plugin.
	 *
	 * @param string $task_id Task id from BlogPlan or CorporatePlan.
	 * @return string URL (may contain `{siteUrl}` for replacement elsewhere in the module).
	 */
	public static function href( string $task_id ): string {
		error_log( 'PlanBrandUrls::href( ' . $task_id . ' )' );
		if ( ! function_exists( 'NewfoldLabs\WP\ModuleLoader\container' ) ) {
			return '';
		}
		else {
			$plugin_id =container()->plugin()->id;
		}
		error_log( 'PlanBrandUrls::href( ' . $task_id . ' ) - plugin_id: ' . $plugin_id );
		if ( self::uses_placeholder_urls( $plugin_id ) ) {
			return self::placeholder_url_for_brand( $plugin_id, $task_id );
		}
		error_log( 'PlanBrandUrls::href( ' . $task_id . ' ) - fallback URL: ' . self::FALLBACK_URLS[ $task_id ] ?? '#' );
		return self::FALLBACK_URLS[ $task_id ] ?? '#';
	}

	/**
	 * @return string Plugin id from container, or empty string if unavailable.
	 */
/* 	private static function resolve_plugin_id(): string {
		if ( ! function_exists( 'NewfoldLabs\WP\ModuleLoader\container' ) ) {
			return '';
		}

		$c = container();
		if ( ! is_object( $c ) || ! method_exists( $c, 'plugin' ) ) {
			return '';
		}

		$plugin = $c->plugin();
		if ( ! is_object( $plugin ) || ! isset( $plugin->id ) ) {
			return '';
		}

		$id = (string) $plugin->id;

		return $id;
	} */

	private static function uses_placeholder_urls( string $plugin_id ): bool {
		return '' !== $plugin_id && in_array( $plugin_id, self::PLACEHOLDER_PLUGIN_IDS, true );
	}

	/**
	 * Placeholder targets for non-Bluehost plugins; replace with real URLs per brand.
	 */
	private static function placeholder_url_for_brand( string $plugin_id, string $task_id ): string {
		if ( 'corporate_install_security_plugin' === $task_id ) {
			return '{siteUrl}/wp-admin/admin.php?page=PLACEHOLDER-' . $plugin_id . '#/marketplace/security';
		}

		return 'https://PLACEHOLDER.invalid/next-steps/' . rawurlencode( $plugin_id ) . '/' . rawurlencode( $task_id );
	}
}

PlanBrandUrls::init();