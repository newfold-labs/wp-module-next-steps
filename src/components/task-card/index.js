import { useState } from 'react';
import { Title, Button } from '@newfold/ui-component-library';
import { TasksModal } from '../tasks-modal';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import { CheckCircleIcon } from '@heroicons/react/24/solid';
import { PaintBrushIcon, CreditCardIcon, ArchiveBoxIcon } from "@heroicons/react/24/outline";
import { customizeYourStoreIcon, addFirstProductIcon, storeSetupPaymentsIcon } from './wireframes';

const ICONS_IDS = {
	'paint-brush': PaintBrushIcon,
	'credit-card': CreditCardIcon,
	'archive-box': ArchiveBoxIcon
}

export const TaskCard = ( {
	id,
	title,
	desc,
	href = '',
	image = null,
	cta = '',
	status = 'new',
	tasks = [],
	wide = false,
	data_attributes: dataAttributes = {},
	className,
	taskUpdateCallback,
	event,
	icon,
	...props
} ) => {

	const [ isModalOpened, setIsModalOpened ] = useState( false );
const Icon = ICONS_IDS[icon] ?? null;
	const getHref = () => {
		let hrefValue = href;
		// replace {siteUrl} placeholder with the actual site URL
		if ( hrefValue.includes( '{siteUrl}' ) ) {
			hrefValue = href.replace( '{siteUrl}', window.NewfoldRuntime.siteUrl );
		}
		return window.NewfoldRuntime?.linkTracker?.addUtmParams( hrefValue ) || hrefValue;
	};

	const getTarget = () => {
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

		if ( href ) {
			attributes[ 'href' ] = getHref();
			attributes[ 'target' ] = getTarget();
		}

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

		return formatted;
	};

	// Combine custom data attributes with any other restProps
	const combinedAttributes = { ...formatDataAttributes() };

	const wireframes = {
		'customize_your_store': customizeYourStoreIcon,
		'add_first_product': addFirstProductIcon,
		'store_setup_payments': storeSetupPaymentsIcon,
	}

	const StepContent = () => {
		return (
			<div className="nfd-nextsteps-step-content nfd-flex nfd-flex-col nfd-justify-between nfd-gap-4">
				<div className="nfd-nextsteps-step-header nfd-flex nfd-align-center nfd-justify-between">
					<span class={'nfd-nextsteps-step-title-wrapper'}>
						{
							Icon &&
							<span className={'nfd-nextsteps-step-icon-wrapper'}>
								<Icon width={ 16 }/>
							</span>
						}
						<Title as="span" className="nfd-nextsteps-step-title nfd-items-center nfd-font-bold nfd-flex nfd-align-center">
							{ title }
						</Title>
					</span>
					{
						'completed' === status &&
						<span className={ 'nfd-nextstep-step__completed-badge nfd-flex nfd-rounded-full nfd-font-bold' }>
							<CheckCircleIcon width={ 24 }/>
							{ __( 'Completed', 'wp-module-next-step' ) }
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
							'nfd-nextsteps-step-card-done': 'completed' === status
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
					<div className="nfd-nextsteps-buttons nfd-flex nfd-justify-center nfd-items-center nfd-gap-2">
						<Button
							as={ 'a' }
							className="nfd-nextsteps-button"
							data-nfd-click="nextsteps_step_link"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							title={ title }
							variant={ 'completed' === status ? 'secondary' : 'primary' }
							onClick={ ( e ) => {
								if ( tasks?.length ) {
									e.preventDefault();
									setIsModalOpened( true );

									return false;
								}

								if ( event ) {
									window.dispatchEvent( new CustomEvent( event ) );
								}
							} }
							{ ...getLinkAttributes() }
						>
							{ cta }
						</Button>
					</div>
				</div>
			</div>
			{
				!! tasks &&
				<TasksModal
					isOpen={ isModalOpened }
					onClose={ () => setIsModalOpened( false ) }
					tasks={ tasks }
					title={ props?.modal_title }
					desc={ props?.modal_desc }
					taskUpdateCallback={ taskUpdateCallback }
				/> }
		</>
	);
};
