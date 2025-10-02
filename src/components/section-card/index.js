import { useState, useEffect } from 'react';
import classNames from 'classnames';
import { Title, Button, Link } from '@newfold/ui-component-library';
import { __ } from '@wordpress/i18n';
import { CheckCircleIcon, XCircleIcon } from '@heroicons/react/24/solid';
import { PaintBrushIcon, CreditCardIcon, ArchiveBoxIcon, ShoppingCartIcon, RocketLaunchIcon, StarIcon, UsersIcon } from '@heroicons/react/24/outline';
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
	complete_on_event: completeOnEvent = '',
	className,
	taskUpdateCallback,
	sectionUpdateCallback,
	icon,
	trackId,
	sectionId,
	isPrimary = false,
	mandatory = false,
	date_completed = null,
	expiryDate = null,
	expiresIn = null,
	nowDate = null,
	index,
	...props
} ) => {

	const [ isModalOpened, setIsModalOpened ] = useState( false );
	const [ eventCompleted, setEventCompleted ] = useState( 'done' === status );

	const Icon = ICONS_IDS[ icon ] ?? null;

	useEffect( () => {

		if ( eventCompleted || ! eventClassToCheck[ id ] ) return;

		const checkElement = () => {
			const el = document.querySelector( eventClassToCheck[ id ] );
			if ( el ) {
				setEventCompleted( true );
				sectionUpdateCallback( trackId, sectionId, 'done' );
				return true;
			}
			return false;
		};

		if ( checkElement() ) {
			return;
		}

		const observer = new MutationObserver( () => {
			if ( checkElement() ) {
				observer.disconnect();
			}
		} );

		observer.observe( document.body, { childList: true, subtree: true } );

		return () => observer.disconnect();
	}, [ eventCompleted ] );

	useEffect( () => {
		if ( tasks.length > 1 || 'done' === status || ! eventCompleted ) {
			return;
		}

		if ( completeOnEvent && ! eventCompleted ) {
			const handleEvent = () => {
				setEventCompleted( true );
				taskUpdateCallback( trackId, sectionId, tasks[ 0 ].id, 'done' );
				sectionUpdateCallback( trackId, sectionId, 'done' );
			};
			document.addEventListener( completeOnEvent, handleEvent );
			return () => document.removeEventListener( completeOnEvent, handleEvent );
		}
	}, [] );


	const getHref = ( href ) => {
		let hrefValue = href;
		// replace {siteUrl} placeholder with the actual site URL
		if ( hrefValue.includes( '{siteUrl}' ) ) {
			hrefValue = href.replace( '{siteUrl}', window.NewfoldRuntime.siteUrl );
		}
		return hrefValue ? window.NewfoldRuntime?.linkTracker?.addUtmParams( hrefValue ) || hrefValue : null;
	};

	const getTarget = ( href ) => {
		// if href is external, return target="_blank"
		if (
			! href ||
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
			attributes[ 'href' ] = getHref( tasks[ 0 ]?.href ? tasks[ 0 ].href : '' );
			attributes[ 'target' ] = getTarget( tasks[ 0 ]?.href ? tasks[ 0 ].href : '' );
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

		if ( 1 === tasks.length && tasks[ 0 ]?.data_attributes ) {
			dataAttributes = { ...dataAttributes, ...(tasks[ 0 ]?.data_attributes ? tasks[ 0 ].data_attributes : {}) };
		}

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
		return cta;
	}

	// Combine custom data attributes with any other restProps
	const combinedAttributes = { ...formatDataAttributes() };

	const wireframes = {
		'customize_your_store': ! wide ? <CustomizeYourStoreWideIcon/> : <CustomizeYourStoreIcon/>,
		'setup_products': ! wide ? <AddFirstProductWideIcon/> : <AddFirstProductIcon/>,
		'setup_payments_shipping': ! wide ? <StoreSetupPaymentsWideIcon/> : <StoreSetupPaymentsIcon/>,
		'store_customize': ! wide ? <StoreSetupShoppingExperienceWideIcon/> : <StoreSetupShoppingExperienceIcon/>,
		'first_marketing_steps': ! wide ? <StoreMarketingStrategyWideIcon/> : <StoreMarketingStrategyIcon/>,
		'store_improve_performance': ! wide ? <StoreImprovePerformanceWideIcon/> : <StoreImprovePerformanceIcon/>,
		'store_collect_reviews': ! wide ? <StoreCollectReviewsWideIcon/> : <StoreCollectReviewsIcon/>,
		'advanced_social_marketing': ! wide ? <StoreLaunchAffiliateWideIcon/> : <StoreLaunchAffiliateIcon/>,
		'next_marketing_steps': ! wide ? <StoreSetupYoastWideIcon/> : <StoreSetupYoastIcon/>,
	}

	// Map of event names to CSS selectors to check for element presence
	const eventClassToCheck = {
		'setup_products': '.nfd-quick-add-product__response-product-permalink',
	}

	const StepContent = () => {
		return (
			<div className="nfd-nextsteps-section-card-content nfd-flex nfd-flex-col nfd-shrink nfd-justify-between nfd-gap-4">
				<div className="nfd-nextsteps-section-card-header nfd-flex nfd-align-center nfd-justify-between">
					<span className={ 'nfd-nextsteps-section-card-title-wrapper' }>
						{
							Icon &&
							<span className={ `nfd-nextsteps-section-card-icon-wrapper nfd-nextsteps-section-card-icon-wrapper-${ icon }` }>
								<Icon width={ 16 }/>
							</span>
						}
						<Title as="span" size="2" className="nfd-nextsteps-section-card-title nfd-items-center nfd-font-bold nfd-flex nfd-align-center nfd-mr-4">
							{ label }
						</Title>
					</span>
					{
						'done' === status &&
						<span className={ 'nfd-nextstep-section-card__completed-badge nfd-flex nfd-rounded-full nfd-font-bold nfd-ml-auto' }>
							<CheckCircleIcon width={ 24 }/>
							{ __( 'Completed', 'wp-module-next-steps' ) }
						</span>
					}
					{
						'dismissed' === status &&
						<span className={ 'nfd-nextstep-section-card__dismissed-badge nfd-flex nfd-rounded-full nfd-font-bold nfd-ml-auto' }>
							<XCircleIcon width={ 24 }/>
							{ __( 'Skipped', 'wp-module-next-steps' ) }
						</span>
					}
				</div>
				<span className="nfd-nextsteps-section-card-description">
					{ desc }
				</span>
			</div>
		);
	};
	/**
	 * Handle card button link click
	 */
	const handleCardLinkClick = ( e ) => {
		// if there are multiple tasks, open the modal
		if ( tasks.length > 1 ) {
			e.preventDefault();
			setIsModalOpened( true );
			return false;
		} else if ( e.target.hasAttribute( 'data-nfd-prevent-default' 	) ) { 
			// if the link has the data-nfd-prevent-default attribute, do not open the link
			return false;
		} else { // if there is only one task
			e.preventDefault();
			// if the status is not done
			let newStatus = status === 'done' ? 'new' : 'done';
			// update the status via section callback
			// tasks will be updated automatically when the section is marked complete
			sectionUpdateCallback(
				trackId,
				sectionId,
				newStatus,
				( er ) => { // error callback
					console.error( 'Error updating section status: ', er );
				},
				( response ) => { // success callback
					// finally open the link
					window.open( e.target.href, '_self' );
				}
			);

			return false;
		}
	}

	return (
		<>
			<div
				className={ classNames(
					className,
					'nfd-nextsteps-section-card-container',
					{
						'nfd-nextsteps-section-card-container--wide': wide,
						'nfd-nextsteps-section-card-container--narrow': ! wide,
					}
				) }
			>
				<div
					id={ `section-card-${ sectionId }` }
					data-nfd-section-id={ sectionId }
					data-nfd-section-index={ index }
					data-nfd-section-status={ status }
					className={ classNames(
						'nfd-nextsteps-section-card nfd-nextsteps-section-card-new',
						{
							'nfd-nextsteps-section-card--wide nfd-flex-col md:nfd-flex-row': wide,
							'nfd-flex-col': ! wide,
							'nfd-nextsteps-section-card-done': 'done' === status,
							'nfd-nextsteps-section-card-dismissed': 'dismissed' === status,
						}
					) }
				>
					{
						wireframes[ id ] &&
						<div className={ classNames(
							'nfd-nextsteps-section-card__wireframe nfd-shrink',
							{
								'nfd-w-full': ! wide,
								'nfd-h-full nfd-w-full': wide,
							} ) }>
							{ wireframes[ id ] }
						</div>
					}
					<StepContent/>
					<div className={ classNames(
						'nfd-nextsteps-buttons nfd-flex nfd-shrink-2 nfd-items-center nfd-gap-2 nfd-justify-between nfd-w-full'
					) }>
						<div className="nfd-nextsteps-buttons-actions-primary nfd-flex">
							<Button
								as={ 'a' }
								className={
									classNames(
										'nfd-nextsteps-button',
										{
											'nfd-nextsteps-button--dismissed': 'dismissed' === status,
											'nfd-nextsteps-button--completed': 'done' === status,
											'nfd-pointer-events-none': 'dismissed' === status,
										}
									)
								}
								data-nfd-click="nextsteps_step_link"
								data-nfd-event-category="nextsteps_step"
								data-nfd-event-key={ id }
								title={ label }
								variant={ isPrimary ? 'primary' : 'secondary' }
								disabled={ 'new' !== status }
								onClick={ handleCardLinkClick }
								{ ...combinedAttributes }
								{ ...getLinkAttributes() }
							>
								{ getCtaText() }
							</Button>
						</div>
						{
							!mandatory &&
							<>
								{
									'dismissed' !== status && <div className="nfd-nextsteps-buttons-actions-secondary">
										<Link
											as="button"
											className="nfd-nextsteps-button nfd-nextsteps-button--skip"
											onClick={ ( e ) => sectionUpdateCallback( trackId, sectionId, 'dismissed' ) }
										>
											{ __( 'Skip it', 'wp-module-next-steps' ) }
										</Link>
									</div>
								}
								{ 'dismissed' === status &&
									<Link
										className="nfd-nextsteps-button nfd-nextsteps-button--undo"
										onClick={ ( e ) => sectionUpdateCallback( trackId, sectionId, 'new' ) }
									>
										{ redoIcon }
										{ __( 'Undo', 'wp-module-next-steps' ) }
									</Link>
								}
							</>
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
					sectionStatus={ status }
					taskUpdateCallback={ taskUpdateCallback }
					sectionUpdateCallback={ sectionUpdateCallback }
				/> }
		</>
	);
};
