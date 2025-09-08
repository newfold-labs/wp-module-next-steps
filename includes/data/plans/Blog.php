<?php

namespace NewfoldLabs\WP\Module\NextSteps\Data\Plans;

use NewfoldLabs\WP\Module\NextSteps\Plan;

/*

# Blog

## Build

### Basic Blog Setup 
- Set site title
- tagline
- time zone
- language

### Customize Your Blog
- Upload Logo                                                                                                                                      
- Choose Colors and Fonts 
- Customize Header and Footer 
- Customize Homepage (select a layout or blog grid) 

### Create Content 

- Add your first blog post 
- Create an “About” page 
- Set a featured image for one post 

### Set Up Navigation 

- Add pages for Home, Blog, About, Contact 
- Create a Primary Menu 
- Create a Footer Menu 

### Set Up Essential Pages 

- Add a Privacy Policy 
- Add Terms & Conditions 
- Add an Accessibility Statement


## Brand

### First Audience‑Building Steps 

- Enable social login  
- Add a welcome‑subscribe popup (convert visitors to email list) 
- Customize notification emails (comment replies, new‑post alerts) 
- Connect Jetpack Stats (or Google Analytics 4) 

### Test Everything 

- Check mobile optimization (link to guide) 
- Click through every menu item & post 
- Disable “Coming Soon” mode when ready 

### Launch & Promote - Social Presence 
- Connect Facebook Page auto‑sharing 
- Connect Instagram auto‑sharing 
- Connect TikTok Profile (optional) 
- Add social‑sharing buttons to posts 
- Embed a social media feed on homepage 

### Launch & Promote - SEO & Visibility 

- Optimize on‑page SEO (Yoast or All‑in‑One SEO) 
- Submit site to Google Search Console 
- Generate & submit XML sitemap 


## Grow

### Enhance Reader Experience 

- Enable & style comments section (Akismet/Antispam Bee) 
- Customize author/profile boxes 
- Display testimonials or highlighted comments 
- Create a Favicon (browser icon) 

### Advanced Promotion & Partnerships 

- Build an email newsletter (Any biz dev here? Beehive is a great free option) 
- Draft an influencer / guest‑post outreach list 
- Run first Facebook or Instagram ad promoting a pillar article 
- Launch a content giveaway (e.g., free e‑book) 
- Track campaigns with UTM links in Analytics 

### Content & Traffic Strategy 

- Plan a content series or editorial calendar 
- Implement internal‑linking strategy for older posts 
- Install Yoast Premium for advanced schemas 

### Performance & Security 

- Speed‑up site with Jetpack Boost / caching plugin 
- Enable automatic backups & update alerts 
- Create a staging site for safe experimentation 

### Blog Analytics 

- Monitor traffic & engagement in Jetpack / GA4  
- Review monthly performance dashboard and set goals

*/

class BlogPlan {

    /**
	 * Get default blog or personal plan
	 *
	 * @return Plan
	 */
    public function __construct() {
        return new Plan(
			array(
				'id'          => 'blog_setup',
				'label'       => __( 'Blog Setup', 'wp-module-next-steps' ),
				'description' => __( 'Get your blog up and running with these essential steps:', 'wp-module-next-steps' ),
				'tracks'      => array(
					array(
						'id'       => 'blog_build_track',
						'label'    => __( 'Build', 'wp-module-next-steps' ),
						'open'     => false,
						'sections' => array(
							array(
								'id'    => 'basic_blog_setup',
								'label' => __( 'Basic Blog Setup', 'wp-module-next-steps' ),
								'open'  => true,
								'tasks' => array(
									array(
										'id'              => 'blog_quick_setup',
										'title'           => __( 'Quick Setup', 'wp-module-next-steps' ),
										'href'            => '{siteUrl}/wp-admin/options-general.php',
										'status'          => 'new',
										'priority'        => 1,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(
											'data-test-id' => 'blog_quick_setup',
											'data-nfd-id'  => 'blog_quick_start',
										),
									),
								),
							),
							array(
								'id'    => 'customize_blog',
								'label' => __( 'Customize Your Blog', 'wp-module-next-steps' ),
								'open'  => true,
								'tasks' => array(
									array(
										'id'       => 'blog_upload_logo',
										'title'    => __( 'Upload Logo', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpattern&postType=wp_template_part&categoryId=all-parts',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_choose_colors_fonts',
										'title'    => __( 'Choose Colors and Fonts', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2Fstyles',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_customize_header',
										'title'    => __( 'Customize Header', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpattern&postType=wp_template_part&categoryId=header',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_customize_footer',
										'title'    => __( 'Customize Footer', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpattern&postType=wp_template_part&categoryId=footer',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'create_content',
								'label' => __( 'Create Content', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'blog_first_post',
										'title'    => __( 'Add Your First Blog Post', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/post-new.php',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_about_page',
										'title'    => __( 'Create an "About" Page', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/post-new.php?post_type=page&wb-library=patterns&wb-category=features',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_set_featured_image',
										'title'    => __( 'Set a Featured Image for One Post', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/post.php?post=1&action=edit',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'setup_navigation',
								'label' => __( 'Set Up Navigation', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'blog_add_pages',
										'title'    => __( 'Add Pages for Home, Blog, About, Contact', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_create_primary_menu',
										'title'    => __( 'Create a Primary Menu', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=/navigation',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_create_footer_menu',
										'title'    => __( 'Create a Footer Menu', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpattern&postType=wp_template_part&categoryId=footer',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'setup_essential_pages',
								'label' => __( 'Set Up Essential Pages', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'blog_privacy_policy',
										'title'    => __( 'Add a Privacy Policy', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/options-privacy.php',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_terms_conditions',
										'title'    => __( 'Add Terms & Conditions', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/post-new.php?wb-library=patterns&wb-category=text',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_accessibility_statement',
										'title'    => __( 'Add an Accessibility Statement', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/post-new.php?wb-library=patterns&wb-category=text',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
						),
					),
					array(
						'id'       => 'blog_brand_track',
						'label'    => __( 'Brand', 'wp-module-next-steps' ),
						'sections' => array(
							array(
								'id'    => 'first_audience_building',
								'label' => __( 'First Audience-Building Steps', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'          => 'blog_welcome_subscribe_popup',
										'title'       => __( 'Add a Welcome-Subscribe Popup', 'wp-module-next-steps' ),
										'description' => __( 'Convert visitors to email subscribers.', 'wp-module-next-steps' ),
										'href'        => 'https://www.bluehost.com/blog/improve-conversion-rate-website-pop-ups/',
										'status'      => 'new',
										'priority'    => 1,
										'source'      => 'wp-module-next-steps',
									),

									/*
									Hide Email Templates and Jetpack Stats for now
									array(
										'id'       => 'blog_customize_notification_emails',
										'title'    => __( 'Customize Notification Emails', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=bh_email_templates_panel',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_connect_jetpack_stats',
										'title'    => __( 'Connect Jetpack Stats (or Google Analytics 4)', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=stats',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
									*/
								),
							),
							array(
								'id'    => 'blog_promote_social',
								'label' => __( 'Social Presence', 'wp-module-next-steps' ),
								'tasks' => array(

									/*
									Hide Jetpack Social Sharing Settings for now
									array(
										'id'       => 'blog_connect_facebook',
										'title'    => __( 'Connect Facebook Page Auto-Sharing', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=jetpack-social',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_add_social_sharing',
										'title'    => __( 'Add Social-Sharing Buttons', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/site-editor.php?p=%2Fstyles&section=%2Fblocks%2Fjetpack%252Fsharing-buttons',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									*/
									array(
										'id'       => 'blog_embed_social_feed',
										'title'    => __( 'Embed a Social Media Feed on Homepage', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/how-to-incorporate-a-social-media-marketing-strategy-with-your-wordpress-website/',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'blog_promote_seo',
								'label' => __( 'SEO & Visibility', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'blog_optimize_seo',
										'title'    => __( 'Optimize On-Page SEO', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=wpseo_dashboard',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_submit_search_console',
										'title'    => __( 'Submit Site to Google Search Console', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/how-to-submit-your-website-to-search-engines/',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_generate_sitemap',
										'title'    => __( 'Generate & Submit XML Sitemap', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/what-is-a-sitemap-how-it-helps-seo-and-navigation/',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
						),
					),
					array(
						'id'       => 'blog_grow_track',
						'label'    => __( 'Grow', 'wp-module-next-steps' ),
						'sections' => array(
							array(
								'id'    => 'enhance_reader_experience',
								'label' => __( 'Enhance Reader Experience', 'wp-module-next-steps' ),
								'tasks' => array(

									/*
									Hide Akismet for now
									array(
										'id'       => 'blog_enable_comments',
										'title'    => __( 'Enable & Style Comments Section', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=akismet-key-config',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									*/
									array(
										'id'       => 'blog_customize_author_boxes',
										'title'    => __( 'Customize Author/Profile Boxes', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2F&canvas=edit',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_display_testimonials',
										'title'    => __( 'Display Testimonials or Highlighted Comments', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/customer-testimonials/',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_create_favicon',
										'title'    => __( 'Create a Favicon', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/customize.php?autofocus[section]=title_tagline',
										'status'   => 'new',
										'priority' => 4,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'advanced_promotion_partnerships',
								'label' => __( 'Advanced Social & Influencer Marketing', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'blog_build_newsletter',
										'title'    => __( 'Build an Email Newsletter', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/how-to-create-an-email-newsletter/',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_draft_outreach_list',
										'title'    => __( 'Draft an Influencer/Guest-Post Outreach List', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/guest-blogging/',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_run_first_ad',
										'title'    => __( 'Run pillar article promotion on social ad', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/social-media-advertising-tips/',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_track_utm_campaigns',
										'title'    => __( 'Track Campaigns with UTM Links', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/how-to-create-a-content-calendar/',
										'status'   => 'new',
										'priority' => 4,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'content_traffic_strategy',
								'label' => __( 'Content & Traffic Strategy', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'blog_plan_content_series',
										'title'    => __( 'Plan a Content Series or Editorial Calendar', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/how-to-create-a-content-calendar/',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_implement_internal_linking',
										'title'    => __( 'Implement Internal-Linking Strategy', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/internal-linking-guide/',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_install_yoast_premium',
										'title'    => __( 'Install Yoast Premium for Advanced Schemas', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=wpseo_dashboard',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'blog_performance_security',
								'label' => __( 'Performance & Security', 'wp-module-next-steps' ),
								'tasks' => array(

									/*
									Hide Jetpack Boost and Automatic Backups for now
									array(
										'id'       => 'blog_speed_up_site',
										'title'    => __( 'Speed-up Site with Jetpack Boost', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=my-jetpack#/add-boost',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'blog_enable_auto_backups',
										'title'    => __( 'Enable Automatic Backups & Update Alerts', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=jetpack-backup',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									*/
									array(
										'id'       => 'blog_create_staging_site',
										'title'    => __( 'Create a Staging Site', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/what-is-a-staging-site-and-how-to-create-a-bluehost-staging-site-for-your-wordpress-website/',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
								),
							),

							/*
							Hide Jetpack Analytics for now
							array(
								'id'    => 'blog_analytics',
								'label' => __( 'Blog Analytics', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'blog_monitor_traffic',
										'title'    => __( 'Monitor Traffic & Engagement', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=stats',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							*/
						),
					),
				),
			)
		);
    }
}
