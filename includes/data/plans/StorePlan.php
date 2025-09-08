<?php

namespace NewfoldLabs\WP\Module\NextSteps\data\plans;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;

/*

# Store

## Build

### Basic Store Setup 
- Quick Setup 

### Customize Your Store 

- Upload Logo 
- Choose Colors and Fonts 
- Customize Header 
- Customize Footer 
- Customize Homepage 

### Set Up Products 

- Add a Product 
- Customize the product page 
- Customize the Shop Page 
- Customize the Cart Page 
- Customize the Checkout Flow 

### Set Up Payments and Shipping 

- Set Up Payments 
- Set Up Shipping 
- Set Up Taxes 

### Set Up Legal Pages 

- Privacy policy 
- Terms & conditions 
- Return and refund policy 


## Brand

### First Marketing steps 

- Enable Social Login Register for your customers 
- Configure Welcome Discount Popup  
- Create a gift card to sell in your shop (gift card) 
- Enable Abandoned Cart Emails 
- Customize your store emails 
- Add Google Analytics

### Launch and promote - Social Media Setup & Engagement 

- Connect Facebook Store
- Connect Instagram Shopping
- Connect Tik Tok Shop
- Add Social Sharing Buttons
- Add Social Media Feed to Homepage

### Launch and promote - SEO & Store Visibility

- Optimize your store SEO
- Submit Site to Google Search Console
- Create a Custom Sitemap


## Grow

### Improve your Customer Experience to sell more 

- Customize the Thank You page 
- Customize your customer’s account page 
- Collect and show reviews for your products

### Advanced Social & Influencer Marketing 

- Launch an affiliate program 
- Create a points & rewards program for your customers 
- Run First Facebook or Instagram Ad 
- Launch Product Giveaway Campaign 
- Create Influencer Outreach List 
- Track UTM Campaign Links 

### Next marketing steps 

- Write a blog Post – Posts page and auto open Wonder Blocks 
- Create a sale & promo campaign for your products 
- Create an upsell campaign 
- Setup Yoast Premium to drive traffic to your store 

### Performance & Security 

- Migliora le performance e la velocità del tuo shop con Jetpack Boost 
- Enable Auto-Backup & Update Alerts – WordPress Core and Code Guard 
- Create a staging website 

### Store analysis 

- Monitor Traffic and Conversion Rates – BH Affiliates Dashboard 
- Run A/B Test on Homepage Banner – Woo Upsell 
- Review Monthly Performance Dashboard

*/

class StorePlan {

    /**
	 * Get default store or ecommerce plan
	 *
	 * @return Plan
	 */
    public static function get_plan() {
		require_once NFD_NEXTSTEPS_DIR . '/includes/DTOs/Plan.php';
        return new Plan(
            array(
				'id'          => 'store_setup',
				'label'       => __( 'Store Setup', 'wp-module-next-steps' ),
				'description' => __( 'Complete your ecommerce store setup with these essential steps:', 'wp-module-next-steps' ),
				'tracks'      => array(
					array( // track
						'id'       => 'store_build_track',
						'label'    => __( 'Build', 'wp-module-next-steps' ),
						'open'     => true,
						'sections' => array(
							array( // section
								'id'          => 'customize_your_store',
								'label'       => __( 'Step 1: Build your Store', 'wp-module-next-steps' ),
								'description' => __( 'Congrats — you’ve built your store! Now it’s time to make it yours. Customize the header, footer, and homepage to give your site a personal touch.', 'wp-module-next-steps' ),
								'cta'         => __( 'Customize Store', 'wp-module-next-steps' ),
								'status'      => 'completed',
								// 'date_completed' => time('2025-09-07 10:00:00'),
								'icon'        => 'paint-brush',
								'modal_title' => __( 'Customize your Store', 'wp-module-next-steps' ),
								'modal_desc'  => __( 'Customize the header, footer, and homepage — just few steps to give your site a personal touch.', 'wp-module-next-steps' ),
								'tasks'       => array(
									array( // task
										'id'              => 'store_upload_logo',
										'title'           => __( 'Upload Logo', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpattern&postType=wp_template_part&categoryId=all-parts',
										'status'          => 'done',
										'priority'        => 1,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
									array( // task
										'id'              => 'store_choose_colors_fonts',
										'title'           => __( 'Choose Colors and Fonts', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => '{siteUrl}/wp-admin/site-editor.php?p=%2Fstyles',
										'status'          => 'done',
										'priority'        => 2,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
									array( // task
										'id'              => 'store_customize_header',
										'title'           => __( 'Customize Header', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpattern&postType=wp_template_part&categoryId=header',
										'status'          => 'new',
										'priority'        => 3,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
									array( // task	
										'id'              => 'store_customize_footer',
										'title'           => __( 'Customize Footer', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpattern&postType=wp_template_part&categoryId=footer',
										'status'          => 'new',
										'priority'        => 4,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
									array( // task
										'id'              => 'store_customize_homepage',
										'title'           => __( 'Customize Homepage', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => '{siteUrl}/wp-admin/site-editor.php?p=%2Ftemplate',
										'status'          => 'new',
										'priority'        => 5,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
								),

							),
							array( // section
								'id'          => 'add_first_product',
								'label'       => __( 'Step 2: Add your first product', 'wp-module-next-steps' ),
								'description' => __( 'Start bringing your store to life by adding a product in just a few simple steps.', 'wp-module-next-steps' ),
								'cta'         => __( 'Add product', 'wp-module-next-steps' ),
								'icon'        => 'archive-box',
								'tasks'       => array(
									array( // task
										'id'              => 'add_first_product_task',
										'title'           => __( 'Add product', 'wp-module-next-steps' ),
										'description'     => __( 'Start bringing your store to life by adding a product in just a few simple steps.', 'wp-module-next-steps' ),
										'href'            => '{siteUrl}/wp-admin/post-new.php?post_type=product',
										'status'          => 'new',
										'priority'        => 1,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(
											'event' => 'nfd-open-quick-add-product-modal',
										),
									),
								),
							),
							array( // section
								'id'          => 'store_setup_payments',
								'label'       => __( 'Step 3: Set Up Payments', 'wp-module-next-steps' ),
								'description' => __( 'Set up payments to start selling — choose your preferred payment methods and connect them in just a few clicks.', 'wp-module-next-steps' ),
								'cta'         => __( 'Set up Payments', 'wp-module-next-steps' ),
								'icon'        => 'credit-card',
								'tasks'       => array(
									array( // task
										'id'              => 'store_setup_payments_task',
										'title'           => __( 'Set up Payments', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => '{siteUrl}/wp-admin/admin.php?page=wc-settings&tab=checkout',
										'status'          => 'new',
										'priority'        => 1,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
								),
							),
							array( // section
								'id'             => 'store_setup_shopping_experience',
								'label'          => __( 'Setup the Shopping Experience', 'wp-module-next-steps' ),
								'description'    => __( 'Personalize your cart and checkout to match your brand and give customers a seamless shopping experience', 'wp-module-next-steps' ),
								'cta'            => __( 'Start Now', 'wp-module-next-steps' ),
								'icon'           => 'shopping-cart',
								'modal_title'    => __( 'Setup the shopping experience', 'wp-module-next-steps' ),
								'modal_desc'     => __( 'Personalize your cart and checkout experience, and configure taxes and shipping options', 'wp-module-next-steps' ),
								'tasks'          => array(
									array( // task
										'id'              => 'store_customize_shop_page',
										'title'           => __( 'Customize the shop page', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) > 0 ? '{siteUrl}/wp-admin/post.php?post=' . wc_get_page_id( 'shop' ) . '&action=edit' : '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status'          => 'new',
										'priority'        => 1,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
									array( // task
										'id'              => 'store_customize_cart_page',
										'title'           => __( 'Customize the cart page', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'cart' ) > 0 ? '{siteUrl}/wp-admin/post.php?post=' . wc_get_page_id( 'cart' ) . '&action=edit' : '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status'          => 'new',
										'priority'        => 2,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
									array( // task
										'id'              => 'store_customize_checkout_page',
										'title'           => __( 'Customize the checkout flow', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'checkout' ) ? '{siteUrl}/wp-admin/post.php?post=' . wc_get_page_id( 'checkout' ) . '&action=edit' : '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status'          => 'new',
										'priority'        => 3,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
								),
							),
							array( // section
								'id'             => 'store_marketing_strategy',
								'label'          => __( 'Build your marketing strategy', 'wp-module-next-steps' ),
								'description'    => __( 'Kickstart your store’s success with smart marketing strategies. Just few steps to build visibility, attract shoppers, and turn visits into loyal sales.', 'wp-module-next-steps' ),
								'cta'            => __( 'Start now', 'wp-module-next-steps' ),
								'icon'           => 'rocket-launch',
								'modal_title'    => __( 'Build your marketing strategy', 'wp-module-next-steps' ),
								'modal_desc'     => __( 'Kickstart your store’s success with smart marketing strategies', 'wp-module-next-steps' ),
								'tasks'          => array(
									array( // task
										'id'              => 'store_marketing_welcome_popup',
										'title'           => __( 'Configure a welcome discount popup', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => '{siteUrl}/wp-admin/post.php?post=',
										'status'          => 'new',
										'priority'        => 1,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
									array( // task
										'id'              => 'store_marketing_gift_card',
										'title'           => __( 'Create a gift card to sell in your shop', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => '{siteUrl}/wp-admin/post.php?post=',
										'status'          => 'new',
										'priority'        => 2,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
									array( // task
										'id'              => 'store_marketing_abandoned_cart_emails',
										'title'           => __( 'Enable abandoned cart emails', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => '{siteUrl}/wp-admin/post.php?post=',
										'status'          => 'new',
										'priority'        => 3,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
									array( // task
										'id'              => 'store_marketing_customize_emails',
										'title'           => __( 'Customize your store emails', 'wp-module-next-steps' ),
										'description'     => '',
										'href'            => '{siteUrl}/wp-admin/admin.php?page=wc-settings&tab=email',
										'status'          => 'new',
										'priority'        => 4,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(),
									),
								),
							),
							array( // section
								'id'             => 'store_improve_performance',
								'label'          => __( 'Improve the performance and speed of your shop', 'wp-module-next-steps' ),
								'description'    => __( 'Speed up your store by optimizing page performance with Jetpack Boost. Easily activate one-click optimizations to boost your Core Web Vitals.', 'wp-module-next-steps' ),
								'href'           => '{siteUrl}/wp-admin/admin.php?page=my-jetpack#add-boost',
								'cta'            => __( 'Start now', 'wp-module-next-steps' ),
								'icon'           => 'jetpack',
								'tasks'          => array(),
							),
							array( // section
								'id'             => 'store_collect_reviews',
								'label'          => __( 'Collect and show reviews for your products', 'wp-module-next-steps' ),
								'description'    => __( 'Collect authentic reviews from your customers to build trust, highlight product quality, and help increase conversions.', 'wp-module-next-steps' ),
								'href'           => '',
								'cta'            => __( 'Start now', 'wp-module-next-steps' ),
								'icon'           => 'start',
								'tasks'          => array(),
							),
							array( // section
								'id'             => 'store_launch_affiliate_program',
								'label'          => __( 'Launch an affiliate program', 'wp-module-next-steps' ),
								'description'    => __( 'Launch your own affiliate program to promote your products, reach new audiences and grow sales.', 'wp-module-next-steps' ),
								'href'           => '',
								'cta'            => __( 'Start now', 'wp-module-next-steps' ),
								'icon'           => 'users',
								'tasks'          => array(),
							),
							array( // section
								'id'             => 'store_setup_yoast_premium',
								'label'          => __( 'Setup Yoast Premium to drive traffic to your store', 'wp-module-next-steps' ),
								'description'    => __( 'Optimize your content for search engines to improve rankings, attract more visitors, and boost your store’s visibility online.', 'wp-module-next-steps' ),
								'href'           => '',
								'cta'            => __( 'Start now', 'wp-module-next-steps' ),
								'icon'           => 'yoast',
								'tasks'          => array(),
							),
						),
					),
				),
			)
		);
    }

}
