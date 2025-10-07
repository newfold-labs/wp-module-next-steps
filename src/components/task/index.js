import { Title } from '@newfold/ui-component-library';
import { __ } from '@wordpress/i18n';
import { useState, useEffect, memo } from '@wordpress/element';
import { doneIcon, hideIcon, showIcon, goIcon, circleDashedIcon, circleIcon, spinner } from '../icons';

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
	const [ isLoading, setIsLoading ] = useState( false );

	useEffect( () => {
		setStatus( task.status );
	}, [ task.status ] );

	/**
	 * Handle link clicks
	 * 
	 * @param {Event} e 
	 * @returns {void}
	 */
	const handleLinkClick = ( e ) => {
		const isCompleteOnClick = e.target.closest( '.nfd-nextsteps-link[data-nfd-complete-on-click]' );
		const isPreventDefault = e.target.closest( '.nfd-nextsteps-link[data-nfd-prevent-default]' );

		// if the link has the data-nfd-complete-on-click attribute and it is set to true
		if ( isCompleteOnClick ) {
			// prevent opening link until status updates
			e.preventDefault();
			// add loading state
			setIsLoading( true );
			// update status via API
			updateStatus(
				e,
				'done',
				( response ) => { // success callback
					// unless prevent default is set
					if ( isPreventDefault ) {
						return false; // restate prevent default
					}
					// then take user to the href
					window.location.href = getHref();
					return true;
				}
			);
			return false; // restate prevent default
		}
		// if the link has the data-nfd-prevent-default attribute, do not open the link
		// there may be a custom listener for this task and it is handled elsewhere
		if ( isPreventDefault ) {
			e.preventDefault();
			return false; // restate prevent default
		}
		// if the data-nfd-complete-on-click attribute is not true or set
		// and data-nfd-prevent-default is not set
		// do nothing, allow link to open, and do not update status
		// there may be custom hooks defined for this task elsewhere
		return true;
	};

	const updateStatus = ( e, newStatus, successCallback = () => {} ) => {
		// Prevent event from bubbling up to parent track details element
		e.stopPropagation();
		
		const previousStatus = status;
		setStatus( newStatus ); // optimistic update - for immediate UI feedback
		// update task status via API
		taskUpdateCallback( 
			trackId,
			sectionId,
			id,
			newStatus,
			( error ) => { // error callback
				// If error, revert optimistic task update to previous status
				setStatus( previousStatus );
				// further error handling done in the error boundary
			},
			( response ) => { // success callback
				setStatus( newStatus ); // redundant since we optimistically set it above
				successCallback( response );
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
	 * Format task attributes for React components
	 */
	const formatDataAttributes = () => {
		return {
			'data-nfd-task-index': index,
			'data-nfd-task-id': id,
			'data-nfd-task-status': status,
		};
	}

	/**
	 * Format data attributes from task data for link
	 * Ensures all keys have 'data-' prefix and handles boolean values
	 */
	const formatLinkDataAttributes = () => {
		const formatted = {};
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

	/**
	 * Render the task content
	 * 
	 * If href is provided, render a link with the href, target, and data attributes
	 * Otherwise, render a span with the title
	 * 
	 * @param {*} href - The href to use for the link
	 * @param {*} target - The target to use for the link
	 * @param {*} dataAttributes - The data attributes to use for the link
	 * @returns 
	 */
	const renderTaskContent = ( href = false, target = '', dataAttributes = {} ) => {
		return (
			<div className="nfd-nextsteps-task-content nfd-flex nfd-flex-col nfd-justify-between">
				{ href && (
					<a
						className="nfd-nextsteps-link"
						data-nfd-click="nextsteps_task_link"
						data-nfd-event-category="nextsteps_task"
						data-nfd-event-key={ id }
						href={ href }
						target={ target }
						{ ...dataAttributes }
						onClick={ ( e ) => {
							handleLinkClick( e );
						} }
					>
						<Title as="span" size="5" className="nfd-nextsteps-task-title nfd-font-normal">
							{ title }
						</Title>
					</a>
				) }
				{ ! href && (
					<Title as="span" size="5" className="nfd-nextsteps-task-title nfd-font-normal">
						{ title }
					</Title>
				) }
				{/* <span>{ description }</span> */}
			</div>
		);
	};
	const renderNewStep = () => {
		return (
			<div
				className="nfd-nextsteps-task-container"
				id={ `task-${ id }` } 
				{ ...formatDataAttributes() }
			>
				<div className="nfd-nextsteps-task nfd-nextsteps-task-new nfd-flex nfd-flex-row nfd-justify-start nfd-items-center nfd-gap-2">
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-end">
						<button
							className="nfd-nextsteps-button nfd-nextsteps-button-todo"
							data-nfd-click="nextsteps_task_check"
							data-nfd-event-category="nextsteps_task"
							data-nfd-event-key={ id }
							onClick={ ( e ) =>
								updateStatus( e, 'done' )
							}
							title={ __(
								'Mark Complete',
								'wp-module-next-steps'
							) }
						>
							{ circleIcon }
						</button>
					</div>
					{ renderTaskContent( getHref(), getTarget(), formatLinkDataAttributes() ) }
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-4 nfd-justify-end nfd-ml-auto">
						{ isLoading ? spinner : (
							<>
								<button
									className="nfd-nextsteps-button nfd-nextsteps-button-dismiss"
									data-nfd-click="nextsteps_task_dismiss"
									data-nfd-event-category="nextsteps_task"
									data-nfd-event-key={ id }
									onClick={ ( e ) =>
										updateStatus( e, 'dismissed' )
									}
									title={ __( 'Skip', 'wp-module-next-steps' ) }
								>
									{ hideIcon }
								</button>
								<a
									className="nfd-nextsteps-button nfd-nextsteps-button-link nfd-nextsteps-link"
									data-nfd-click="nextsteps_task_link"
									data-nfd-event-category="nextsteps_task"
									data-nfd-event-key={ id }
									{ ...formatLinkDataAttributes() }
									href={ getHref() }
									onClick={ (e) => {
										handleLinkClick( e );
									}}
									target={ getTarget() }
									title={ title }
								>
									{ goIcon }
								</a>
							</>
						) }
					</div>
				</div>
			</div>
		);
	};
	const renderDoneStep = () => {
		return (
			<div
				className="nfd-nextsteps-task-container"
				id={ `task-${ id }` } 
				{ ...formatDataAttributes() }
			>
				<div className="nfd-nextsteps-task nfd-nextsteps-task-done nfd-flex nfd-flex-row nfd-justify-start nfd-items-center nfd-gap-2">
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-end">
						<button
							className="nfd-nextsteps-button nfd-nextsteps-button-redo"
							data-nfd-click="nextsteps_task_redo"
							data-nfd-event-category="nextsteps_task"
							data-nfd-event-key={ id }
							onClick={ ( e ) =>
								updateStatus( e, 'new' )
							}
							title={ __( 'Restart', 'wp-module-next-steps' ) }
						>
							{ doneIcon }
						</button>
					</div>
					{ renderTaskContent() }
                    <div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-4 nfd-justify-end nfd-ml-auto">
						{ isLoading && spinner }
						{/* No buttons needed for task that is already complete */}
                    </div>
				</div>
			</div>
		);
	};
	const renderDismissedStep = () => {
		return (
			<div
				className="nfd-nextsteps-task-container"
				id={ `task-${ id }` } 
				{ ...formatDataAttributes() }
			>
				<div className="nfd-nextsteps-task nfd-nextsteps-task-dismissed nfd-flex nfd-flex-row nfd-justify-start nfd-items-center nfd-gap-2">
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-end">
						<button
							className="nfd-nextsteps-button nfd-nextsteps-button-redo"
							data-nfd-click="nextsteps_task_redo"
							data-nfd-event-category="nextsteps_task"
							data-nfd-event-key={ id }
							onClick={ ( e ) =>
								updateStatus( e, 'new' )
							}
							title={ __( 'Unskip', 'wp-module-next-steps' ) }
						>
							{ circleDashedIcon }
						</button>
					</div>
					{ renderTaskContent() }
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-4 nfd-justify-end nfd-ml-auto">
						{ isLoading ? spinner : (
							<button
								className="nfd-nextsteps-button nfd-nextsteps-button-dismiss"
								data-nfd-click="nextsteps_task_dismiss"
								data-nfd-event-category="nextsteps_task"
								data-nfd-event-key={ id }
								onClick={ ( e ) =>
									updateStatus( e, 'new' )
								}
								title={ __( 'Unskip', 'wp-module-next-steps' ) }
							>
								{ showIcon }
							</button>
						) }
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
