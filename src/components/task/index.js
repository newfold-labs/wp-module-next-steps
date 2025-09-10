import { Title } from '@newfold/ui-component-library';
import { __ } from '@wordpress/i18n';
import { useState, useEffect, memo } from '@wordpress/element';
import { doneIcon, hideIcon, showIcon, goIcon, circleDashedIcon, circleIcon } from '../icons';

export const Task = memo(( props ) => {
	const {
		index,
		sectionId,
		showDismissed,
		task,
		taskUpdateCallback,
		trackId,
	} = props;
	
	// Destructure task properties
	const {
		id,
		title = '',
		href,
		data_attributes = {}
	} = task;
	// task status uses state to track the current status
	const [ status, setStatus ] = useState( task.status );

	useEffect( () => {
		setStatus( task.status );
	}, [ task.status ] );

	const updateStatus = ( newStatus ) => {
		const previousStatus = status;
		setStatus( newStatus ); // optimistic update - for immediate UI feedback
		// update task status via API
		taskUpdateCallback( 
			trackId,
			sectionId,
			id,
			newStatus,
			( error ) => {
				// If error, revert optimistic task update to previous status
				setStatus( previousStatus );
				// further error handling done in the error boundary
			},
			( response ) => {
				setStatus( newStatus ); // redundant since we optimistically set it above
			}
		);
	};
	
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

	/**
	 * Format data attributes for React components
	 * Ensures all keys have 'data-' prefix and handles boolean values
	 */
	const formatDataAttributes = () => {
		const formatted = {
			'data-nfd-task-index': index,
			'data-nfd-task-id': id,
			'data-nfd-task-status': status,
		};
		
		Object.entries( data_attributes ).forEach( ( [ key, value ] ) => {
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

	const renderStepContent = ( href = false, target = '' ) => {
		return (
			<div className="nfd-nextsteps-step-content nfd-flex nfd-flex-col nfd-justify-between">
				{ href && (
					<a href={ href } target={ target }>
						<Title as="span" className="nfd-nextsteps-step-title nfd-font-normal">
							{ title }
						</Title>
					</a>
				) }
				{ ! href && (
					<Title as="span" className="nfd-nextsteps-step-title nfd-font-normal">
						{ title }
					</Title>
				) }
				{/* <span>{ description }</span> */}
			</div>
		);
	};
	const renderNewStep = () => {
		return (
			<div className="nfd-nextsteps-step-container" id={ id } { ...combinedAttributes }>
				<div className="nfd-nextsteps-step nfd-nextsteps-step-new nfd-flex nfd-flex-row nfd-justify-start nfd-items-center nfd-gap-4">
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-end">
						<button
							className="nfd-nextsteps-button nfd-nextsteps-button-todo"
							data-nfd-click="nextsteps_step_check"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							onClick={ ( e ) =>
								updateStatus( 'done' )
							}
							title={ __(
								'Mark Complete',
								'wp-module-next-steps'
							) }
						>
							{ circleIcon }
						</button>
					</div>
					{ renderStepContent( getHref(), getTarget() ) }
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-4 nfd-justify-end nfd-ml-auto">
						<button
							className="nfd-nextsteps-button nfd-nextsteps-button-dismiss"
							data-nfd-click="nextsteps_step_dismiss"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							onClick={ ( e ) =>
								updateStatus( 'dismissed' )
							}
							title={ __( 'Skip', 'wp-module-next-steps' ) }
						>
							{ hideIcon }
						</button>
						<a
							className="nfd-nextsteps-button nfd-nextsteps-button-link"
							data-nfd-click="nextsteps_step_link"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							href={ getHref() }
							target={ getTarget() }
							title={ title }
						>
							{ goIcon }
						</a>
					</div>
				</div>
			</div>
		);
	};
	const renderDoneStep = () => {
		return (
			<div className="nfd-nextsteps-step-container" id={ id } { ...combinedAttributes }>
				<div className="nfd-nextsteps-step nfd-nextsteps-step-done nfd-flex nfd-flex-row nfd-justify-start nfd-items-center nfd-gap-4">
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-end">
						<button
							className="nfd-nextsteps-button nfd-nextsteps-button-redo"
							data-nfd-click="nextsteps_step_redo"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							onClick={ ( e ) =>
								updateStatus( 'new' )
							}
							title={ __( 'Restart', 'wp-module-next-steps' ) }
						>
							{ doneIcon }
						</button>
					</div>
					{ renderStepContent( getHref(), getTarget() ) }
                    <div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-4 nfd-justify-end nfd-ml-auto">
                        <a
                            className="nfd-nextsteps-button nfd-nextsteps-button-link"
                            data-nfd-click="nextsteps_step_link"
                            data-nfd-event-category="nextsteps_step"
                            data-nfd-event-key={ id }
                            href={ getHref() }
                            target={ getTarget() }
                            title={ title }
                        >
                            { goIcon }
                        </a>
                    </div>
				</div>
			</div>
		);
	};
	const renderDismissedStep = () => {
		return (
			<div className="nfd-nextsteps-step-container" id={ id } { ...combinedAttributes }>
				<div className="nfd-nextsteps-step nfd-nextsteps-step-dismissed nfd-flex nfd-flex-row nfd-justify-start nfd-items-center nfd-gap-4">
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-end">
						<button
							className="nfd-nextsteps-button nfd-nextsteps-button-redo"
							data-nfd-click="nextsteps_step_redo"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							onClick={ ( e ) =>
								updateStatus( 'new' )
							}
							title={ __( 'Unskip', 'wp-module-next-steps' ) }
						>
							{ circleDashedIcon }
						</button>
					</div>
					{ renderStepContent( getHref(), getTarget() ) }
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-4 nfd-justify-end nfd-ml-auto">
						<button
							className="nfd-nextsteps-button nfd-nextsteps-button-dismiss"
							data-nfd-click="nextsteps_step_dismiss"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							onClick={ ( e ) =>
								updateStatus( 'new' )
							}
							title={ __( 'Unskip', 'wp-module-next-steps' ) }
						>
							{ showIcon }
						</button>
					</div>
				</div>
			</div>
		);
	};

	return (
		<>
			{ status === 'new' && renderNewStep() }
			{ status === 'done' && renderDoneStep() }
			{ status === 'dismissed' && showDismissed && renderDismissedStep() }
		</>
	);
});
