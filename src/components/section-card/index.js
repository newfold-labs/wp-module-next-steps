import { useState, useEffect } from 'react';
import classNames from 'classnames';
import { Title, Button, Link } from '@newfold/ui-component-library';
import { __ } from '@wordpress/i18n';
import { CheckCircleIcon, XCircleIcon } from '@heroicons/react/24/solid';
import { PaintBrushIcon, CreditCardIcon, ArchiveBoxIcon, ShoppingCartIcon, RocketLaunchIcon, StarIcon, UsersIcon } from '@heroicons/react/24/outline';
import { spinner } from '../icons';
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

/**
 * Render the completed badge
 *
 * @param {object} props - The props for the completed badge
 * @returns {JSX.Element}
 */
const CompletedBadge = ({ className, ...props }) => (
	<span className={ 'nfd-nextstep-section-card__completed-badge nfd-flex nfd-rounded-full nfd-font-bold nfd-ml-auto ' + className  } {...props}>
		<CheckCircleIcon width={ 24 }/>
		{ __( 'Completed', 'wp-module-next-steps' ) }
	</span>
);

/**
 * Render the dismissed badge
 *
 * @param {object} props - The props for the dismissed badge
 * @returns {JSX.Element}
 */
const DismissedBadge = ( { className, ...props }) => (
	<span className={ 'nfd-nextstep-section-card__dismissed-badge nfd-flex nfd-rounded-full nfd-font-bold nfd-ml-auto ' + className } {...props}>
		<XCircleIcon width={ 24 }/>
		{ __( 'Skipped', 'wp-module-next-steps' ) }
	</span>
);

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
	const [ isLoading, setIsLoading ] = useState( false );

	const Icon = ICONS_IDS[ icon ] ?? null;

	/**
	 * Handle the complete on event
	 */
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


	/**
	 * Get the href for the link
	 *
	 * @param {string} href - The href value to process for a link
	 * @returns {string}
	 */
	const getHref = ( href ) => {
		let hrefValue = href;
		// replace {siteUrl} placeholder with the actual site URL
		if ( hrefValue.includes( '{siteUrl}' ) ) {
			hrefValue = href.replace( '{siteUrl}', window.NewfoldRuntime.siteUrl );
		}
		return hrefValue ? window.NewfoldRuntime?.linkTracker?.addUtmParams( hrefValue ) || hrefValue : null;
	};

	/**
	 * Get the target for the link
	 *
	 * @param {string} href - The href to get the target for
	 * @returns {string}
	 */
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

	/**
	 * Format data attributes for React components
	 * Ensures all keys have 'data-' prefix and handles boolean values
	 *
	 * @returns {object}
	 */
	const formatCardDataAttributes = () => {
		const formatted = {};

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
	 * Get task link attributes
	 * If this section has only one task, add href and target for single task
	 *
	 * @returns {object}
	 */
	const getTaskLinkAttributes = () => {
		const attributes = {};
		// if this section has only one task, add href and target for single task
		if ( tasks.length <= 1 ) {
			attributes[ 'href' ] = getHref( tasks[ 0 ]?.href ? tasks[ 0 ].href : '' );
			attributes[ 'target' ] = getTarget( tasks[ 0 ]?.href ? tasks[ 0 ].href : '' );
		}
		return attributes;
	}

	/**
	 * Formats link attributes for React components.
	 * 
	 * This method handles two main responsibilities:
	 * 1. For single-task sections: adds href/target from the task
	 * 2. Formats all data attributes with proper 'data-' prefix and boolean handling
	 *
	 * @returns {object} Combined attributes object with href/target and formatted data attributes
	 */
	const formatLinkDataAttributes = () => {
		// Step 0: only return attributes if status is new
		if ( 'new' !== status ) {
			return {};
		}

		// Step 1: Handle single-task sections - add href and target attributes
		const linkAttributes = {};
		let combinedDataAttributes = dataAttributes;
		
		if ( tasks.length === 1 ) {
			const task = tasks[ 0 ];
			linkAttributes.href = getHref( task?.href || '' );
			linkAttributes.target = getTarget( task?.href || '' );
			
			// Merge task data attributes (task attributes override section attributes)
			if ( task?.data_attributes ) {
				// combinedDataAttributes contains the section data attributes
                // task.data_attributes is the task data attributes
                // last item in spread overrides earlier items in spread
                // so task attributes override section attributes if there are any matching keys
				combinedDataAttributes = { ...combinedDataAttributes, ...task.data_attributes };
			}
		}

		// Step 2: Format all data attributes with proper prefix and type handling
		const formattedDataAttributes = {};
		Object.entries( combinedDataAttributes ).forEach( ( [ key, value ] ) => {
			// Ensure all keys have 'data-' prefix for HTML compliance
			const dataKey = key.startsWith( 'data-' ) ? key : `data-${ key }`;

			// Convert boolean values to strings (React/HTML requirement)
			if ( typeof value === 'boolean' ) {
				formattedDataAttributes[ dataKey ] = value.toString();
			} else {
				formattedDataAttributes[ dataKey ] = value;
			}
		} );

		// Step 3: Combine link attributes with formatted data attributes
		return { ...linkAttributes, ...formattedDataAttributes };
	};

	/**
	 * Adjust CTA text based on status
	 *
	 * @returns {string}
	 */
	const getCtaText = () => {
		return cta;
	}

	/**
	 * Determine the wireframe based on id and wide prop
	 *
	 * @returns {JSX.Element}
	 */
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
	};

	/**
	 * Render the step content
	 *
	 * @returns {JSX.Element}
	 */
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
					{ 'done' === status && ! wide && <CompletedBadge/> }
					{ 'dismissed' === status && ! wide && <DismissedBadge/> }
				</div>
				<span className="nfd-nextsteps-section-card-description">
					{ desc }
				</span>
			</div>
		);
	};

	/**
	 * Handle card button link click
	 * 
	 * @param {Event} e - The event object
	 * @returns {boolean}
	 */
	const handleCardLinkClick = ( e ) => {
		// Modal behavior - MULTIPLE TASKS
		// if there are multiple tasks, open the modal
		if ( tasks.length > 1 ) {
			e.preventDefault();
			// open tasks modal
			setIsModalOpened( true );
			return false;
		}

		const isCompleteOnClick = e.target.closest( '.nfd-nextsteps-link[data-nfd-complete-on-click="true"]' );
		const isPreventDefault = e.target.closest( '.nfd-nextsteps-link[data-nfd-prevent-default="true"]' );

		// Link behavior - SINGLE TASK
		// with data-nfd-complete-on-click set to true
		if ( isCompleteOnClick ) {
			e.preventDefault();
			// add loading state
			setIsLoading( true );
			// update the status via section callback
			// note: tasks in this section will be updated also
			sectionUpdateCallback(
				trackId,
				sectionId,
				'done',
				( er ) => { // error callback
					console.error( 'Error updating section status: ', er );
					setIsLoading( false );
				},
				( response ) => { // success callback
					// then take user to the href, unless data-nfd-prevent-default is set
					if ( isPreventDefault ) {
						setIsLoading( false );
						return false;
					}
					const linkElement = e.target.closest( 'a[href]' );
					if ( linkElement ) {
						window.location.href = linkElement.getAttribute( 'href' );
					} else {
						console.warn( 'No link/href element found for navigation' );
						setIsLoading( false );
					}
				}
			);
		}
		// if the link has the data-nfd-prevent-default attribute, but not data-nfd-complete-on-click,
		// still do not open the link, there may be a custom listener for this section/task and it is handled elsewhere
		if ( isPreventDefault ) {
			e.preventDefault();
			return false;
		}
		// if there is only one task and the data-nfd-complete-on-click attribute is not true or set
		// and data-nfd-prevent-default is not set
		// do nothing, allow link to open, and do not update status
		// there may be custom hooks defined for this task elsewhere
		return true;
	}

	/**
	 * Render the section card
	 *
	 * @returns {JSX.Element}
	 */
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
					{ ...formatCardDataAttributes() }
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
						'nfd-nextsteps-buttons nfd-flex nfd-shrink-2 nfd-items-center nfd-gap-2 nfd-justify-between nfd-w-full nfd-relative'
					) }>
						{ 'done' === status && wide && <CompletedBadge className={'nfd-absolute nfd-top-0 nfd-right-0'}/> }
						{ 'dismissed' === status && wide && <DismissedBadge className={'nfd-absolute nfd-top-0 nfd-right-0'}/> }
						<div className="nfd-nextsteps-buttons-actions-primary nfd-flex">
							<Button
								as={ 'a' }
								className={
									classNames(
										'nfd-nextsteps-button nfd-nextsteps-link',
										{
											'nfd-nextsteps-button--dismissed': 'dismissed' === status,
											'nfd-nextsteps-button--completed': 'done' === status,
											'nfd-pointer-events-none': 'dismissed' === status,
										}
									)
								}
								data-nfd-click={ 'nextsteps_step_link' }
								data-nfd-event-category={ 'nextsteps_step' }
								data-nfd-event-key={ id }
								title={ label }
								variant={ isPrimary ? 'primary' : 'secondary' }
								disabled={ 'new' !== status || isLoading }
								onClick={ 'new' === status ? handleCardLinkClick : null }
								{ ...formatLinkDataAttributes() }
							>
								{ getCtaText() }
								{ isLoading && spinner }
							</Button>
						</div>
						{
							! mandatory && 'done' !== status &&
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
