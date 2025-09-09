import { useState, useEffect } from 'react';
import classNames from 'classnames';
import { Title, Button, Link } from '@newfold/ui-component-library';
import { __ } from '@wordpress/i18n';
import { CheckCircleIcon, XCircleIcon } from '@heroicons/react/24/solid';
import { PaintBrushIcon, CreditCardIcon, ArchiveBoxIcon, ShoppingCartIcon, RocketLaunchIcon, StarIcon, UsersIcon } from "@heroicons/react/24/outline";
import { TasksModal } from '../tasks-modal';
import { 
	CustomizeYourStoreIcon,
	CustomizeYourStoreWideIcon,
	AddFirstProductIcon,
	AddFirstProductWideIcon,
	StoreSetupPaymentsIcon,
	StoreSetupPaymentsWideIcon,
	StoreSetupShoppingExperienceIcon,
	StoreSetupShoppingExperienceWideIcon,
	StoreMarketingStrategyIcon,
	StoreMarketingStrategyWideIcon,
	StoreCollectReviewsIcon,
	StoreCollectReviewsWideIcon,
	StoreLaunchAffiliateIcon,
	StoreLaunchAffiliateWideIcon,
	StoreSetupYoastIcon,
	StoreSetupYoastWideIcon,
	StoreImprovePerformanceIcon,
	StoreImprovePerformanceWideIcon
} from './wireframes';
import { redoIcon, jetPackIcon, yoastIcon } from '../icons';

const ICONS_IDS = {
	'paint-brush': PaintBrushIcon,
	'credit-card': CreditCardIcon,
	'archive-box': ArchiveBoxIcon,
	'shopping-cart': ShoppingCartIcon,
	'rocket-launch': RocketLaunchIcon,
	'star': StarIcon,
	'users': UsersIcon,
	'jetpack': jetPackIcon,
	'yoast': yoastIcon,
}

export const SectionCard = ( {
	id,
	label,
	desc,
	image = null,
	cta = '',
	status = 'new',
	tasks = [],
	wide = false,
	data_attributes: dataAttributes = {},
	className,
	taskUpdateCallback,
	sectionUpdateCallback,
	icon,
	trackId,
	sectionId,
	isPrimary = false,
	date_completed = null,
	expiryDate = null,
	expiresIn = null,
	nowDate = null,
	...props
} ) => {

	const [ isModalOpened, setIsModalOpened ] = useState( false );
	const [ eventCompleted, setEventCompleted ] = useState( 'done' === status );

	const Icon = ICONS_IDS[icon] ?? null;

	useEffect(() => {

		if ( eventCompleted || !eventClassToCheck[id] ) return;

		const checkElement = () => {
			const el = document.querySelector( eventClassToCheck[id] );
			if (el) {
				setEventCompleted(true);
				sectionUpdateCallback( trackId, sectionId, 'done' );
				return true;
			}
			return false;
		};

		if ( checkElement() ) {
			return;
		}

		const observer = new MutationObserver(() => {
			if ( checkElement() ) {
				observer.disconnect();
			}
		});

		observer.observe(document.body, { childList: true, subtree: true });

		return () => observer.disconnect();
	}, [eventCompleted]);


	const getHref = ( href ) => {
		let hrefValue = href;
		// replace {siteUrl} placeholder with the actual site URL
		if ( hrefValue.includes( '{siteUrl}' ) ) {
			hrefValue = href.replace( '{siteUrl}', window.NewfoldRuntime.siteUrl );
		}
		return window.NewfoldRuntime?.linkTracker?.addUtmParams( hrefValue ) || hrefValue;
	};

	const getTarget = ( href ) => {
		// if href is external, return target="_blank"
		if (
			href.includes( '{siteUrl}' ) ||
			href.includes( window.NewfoldRuntime.siteUrl )
		) {
			return '';
		}
		return '_blank';
	};

	const getLinkAttributes = () => {
		const attributes = {};
		// if this section has only one task, add href and target for single task
		if ( tasks.length <= 1 ) {
			attributes[ 'href' ] = getHref( tasks[0]?.href ? tasks[0].href : '' );
			attributes[ 'target' ] = getTarget( tasks[0]?.href ? tasks[0].href : '' );
		}
		// Only add href and target if href is provided and either no event is set or status is 'done'
		// if ( href && ( !event || ( event && 'done' === status ) ) ) {
		// 	attributes[ 'href' ] = getHref();
		// 	attributes[ 'target' ] = getTarget();
		// }
		return attributes;
	}

	/**
	 * Format data attributes for React components
	 * Ensures all keys have 'data-' prefix and handles boolean values
	 */
	const formatDataAttributes = () => {
		const formatted = {};

		Object.entries( dataAttributes ).forEach( ( [ key, value ] ) => {
			// Ensure key has 'data-' prefix
			const dataKey = key.startsWith( 'data-' ) ? key : `data-${ key }`;

			// Handle boolean values (convert to string or use key as flag)
			if ( typeof value === 'boolean' ) {
				formatted[ dataKey ] = value ? 'true' : 'false';
			} else {
				formatted[ dataKey ] = value;
			}
		} );

		if ( date_completed ) {
			formatted[ 'data-nfd-date-completed' ] = date_completed;
		}
		if ( expiryDate ) {
			formatted[ 'data-nfd-expiry-date' ] = expiryDate;
		}
		if ( expiresIn ) {
			formatted[ 'data-nfd-expires-in' ] = expiresIn;
		}
		if ( nowDate ) {
			formatted[ 'data-nfd-now-date' ] = nowDate;
		}

		return formatted;
	};

	/**
	 * Adjust CTA text based on status
	 */
	const getCtaText = () => {
		let ctaText = cta;
		// if( 'dismissed' === status ) {
		// 	ctaText = __('SKIPPED', 'wp-module-next-step');
		// }
		// Change CTA text for completed "Add your first product" step
		// if( 'done' === status && 'add_first_product' === id ) {
		// 	ctaText = __('Add another product', 'wp-module-next-step');
		// }
		return ctaText;
	}

	// Combine custom data attributes with any other restProps
	const combinedAttributes = { ...formatDataAttributes() };

	const wireframes = {
		'customize_your_store': !wide ? <CustomizeYourStoreWideIcon /> : <CustomizeYourStoreIcon />,
		'add_first_product': !wide ? <AddFirstProductWideIcon /> : <AddFirstProductIcon />,
		'store_setup_payments': !wide ? <StoreSetupPaymentsWideIcon /> : <StoreSetupPaymentsIcon />,
		'store_setup_shopping_experience' : !wide ? <StoreSetupShoppingExperienceWideIcon /> : <StoreSetupShoppingExperienceIcon />,
		'store_marketing_strategy' : !wide ? <StoreMarketingStrategyWideIcon /> : <StoreMarketingStrategyIcon />,
		'store_collect_reviews' : !wide ? <StoreCollectReviewsWideIcon /> : <StoreCollectReviewsIcon />,
		'store_launch_affiliate_program' : !wide ? <StoreLaunchAffiliateWideIcon /> : <StoreLaunchAffiliateIcon />,
		'store_setup_yoast_premium' : !wide ? <StoreSetupYoastWideIcon /> : <StoreSetupYoastIcon />,
		'store_improve_performance' : !wide ? <StoreImprovePerformanceWideIcon /> : <StoreImprovePerformanceIcon />,
	}

	// Map of event names to CSS selectors to check for element presence
	const eventClassToCheck = {
		'add_first_product' : '.nfd-quick-add-product__response-product-permalink',
	}

	const StepContent = () => {
		return (
			<div className="nfd-nextsteps-step-content nfd-flex nfd-flex-col nfd-justify-between nfd-gap-4">
				<div className="nfd-nextsteps-step-header nfd-flex nfd-align-center nfd-justify-between">
					<span className={'nfd-nextsteps-step-title-wrapper'}>
						{
							Icon &&
							<span className={`nfd-nextsteps-step-icon-wrapper nfd-nextsteps-step-icon-wrapper-${icon}`}>
								<Icon width={ 16 }/>
							</span>
						}
						<Title as="span" className="nfd-nextsteps-step-title nfd-items-center nfd-font-bold nfd-flex nfd-align-center nfd-mr-4">
							{ label }
						</Title>
					</span>
					{
						'done' === status &&
						<span className={ 'nfd-nextstep-step__completed-badge nfd-flex nfd-rounded-full nfd-font-bold nfd-ml-auto' }>
							<CheckCircleIcon width={ 24 }/>
							{ __( 'Completed', 'wp-module-next-step' ) }
						</span>
					}
					{
						'dismissed' === status &&
						<span className={ 'nfd-nextstep-step__dismissed-badge nfd-flex nfd-rounded-full nfd-font-bold nfd-ml-auto' }>
							<XCircleIcon width={ 24 }/>
							{ __( 'Skipped', 'wp-module-next-step' ) }
						</span>
					}
				</div>
				<span>{ desc }</span>
			</div>
		);
	};

	return (
		<>
			<div className={ classNames( className, 'nfd-nextsteps-step-container' ) } id={ id } { ...combinedAttributes }>
				<div
					className={ classNames(
						'nfd-nextsteps-step-card nfd-nextsteps-step-card-new nfd-h-full nfd-flex nfd-justify-between nfd-items-start nfd-gap-4 nfd-h-full',
						{
							'nfd-nextsteps-step-card--wide nfd-flex-row': wide,
							'nfd-flex-col': ! wide,
							'nfd-nextsteps-step-card-done': 'done' === status,
							'nfd-nextsteps-step-card-dismissed': 'dismissed' === status,
						}
					) }
				>
					{
						wireframes[ id ] &&
						<div className={ 'nfd-nextsteps-step-card__wireframe' }>
							{ wireframes[ id ] }
						</div>
					}
					<StepContent/>
					<div className={ classNames(
							 'nfd-nextsteps-buttons nfd-flex nfd-items-center nfd-gap-2',
							 {
								 'nfd-justify-between' : !wide,
								 'nfd-w-full': !wide,
								 'nfd-justify-center' : wide,
								 'nfd-flex-col': wide,
							 }
						 ) }>
						<div className="nfd-nextsteps-buttons-actions-primary nfd-flex">
							<Button
								as={ 'a' }
								className= {
									classNames(
										'nfd-nextsteps-button',
										{
											'nfd-nextsteps-button--dismissed': 'dismissed' === status,
											'nfd-nextsteps-button--completed': 'done' === status,
											'nfd-pointer-events-none' : 'dismissed' === status,
										}
									)
								}
								data-nfd-click="nextsteps_step_link"
								data-nfd-event-category="nextsteps_step"
								data-nfd-event-key={ id }
								title={ label }
								variant={ isPrimary ? 'primary' : 'secondary' }
								disabled={ 'new' !== status }
								onClick={ ( e ) => {
									if ( tasks.length > 1 ) {
										e.preventDefault();
										setIsModalOpened( true );
										return false;
									}
									if ( 'done' !== status ) {
										taskUpdateCallback( trackId, sectionId, tasks[0].id, 'done' );
										sectionUpdateCallback( trackId, sectionId, 'done' );
										window.dispatchEvent( new CustomEvent( event ) );
									}
								} }
								{ ...getLinkAttributes() }
							>
								{ getCtaText() }
							</Button>
						</div>
						{
							'dismissed' !== status && <div className="nfd-nextsteps-buttons-actions-secondary">
							<Link
								as="button"
								className="nfd-nextsteps-button nfd-nextsteps-button--skip"
								onClick={(e) => sectionUpdateCallback( trackId, sectionId, 'dismissed' ) }
							>
								{ __('Skip it', 'wp-module-next-step') }
							</Link>
							</div>
						}
						{ 'dismissed' === status &&
							<Link
								className= 'nfd-nextsteps-button nfd-nextsteps-button--undo'
								onClick={ ( e ) => sectionUpdateCallback( trackId, sectionId, 'new' ) }
							>
								{ redoIcon }
								{ __('Undo', 'wp-module-next-step') }
							</Link>
						}
					</div>
				</div>
			</div>
			{
				!! tasks && tasks.length > 1 &&
				<TasksModal
					isOpen={ isModalOpened }
					onClose={ () => setIsModalOpened( false ) }
					tasks={ tasks }
					title={ props?.modal_title }
					desc={ props?.modal_desc }
					trackId={ trackId }
					sectionId={ sectionId }
					taskUpdateCallback={ taskUpdateCallback }
					sectionUpdateCallback={ sectionUpdateCallback }
				/> }
		</>
	);
};
