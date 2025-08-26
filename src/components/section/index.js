import { useEffect, useState, useRef, memo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Title } from '@newfold/ui-component-library';
import { plusCircleIcon, minusCircleIcon, closeCircleIcon,trophyIcon } from '../icons';
import { ProgressBar } from '../progressBar';
import { Task } from '../task';
import { ErrorBoundary } from '../ErrorBoundary';

export const Section = memo(( props ) => {
	const {
		index,
		section,
		sectionOpenCallback,
		showDismissed,
		taskUpdateCallback,
		trackId,
	} = props;
	
	// Get progress data from props (calculated in parent)
	const { totalCount, completedCount, isComplete } = section.progress || {
		totalCount: 0,
		completedCount: 0,
		isComplete: false
	};
	
	const [ showCompleteCelebration, setShowCompleteCelebration ] = useState( false );
	// Track the previous completion state to detect user-triggered completions
	const prevIsComplete = useRef( isComplete );
	const isInitialMount = useRef( true );

	// watch for section completion state changes and display success celebration if needed
	useEffect( () => {
		// Only show celebration if
		if ( 
			isComplete && // Section is now complete
			totalCount > 0 && // Has tasks to complete  
			!prevIsComplete.current && // Was previously incomplete (user-triggered transition)
			!isInitialMount.current // Not the initial mount
		) {
			// display success celebration (slight css-base delay and animation)
			setShowCompleteCelebration( true );
		}
		
		// Update refs for next render
		prevIsComplete.current = isComplete;
		isInitialMount.current = false;
	}, [ isComplete ] );

	const handleToggleOpen = ( event ) => {
		// Prevent event from bubbling up to parent track details element
		event.stopPropagation();
		
		// Get the new open state from the details element
		const newOpenState = event.target.open;
		// Call the callback to update the backend
		sectionOpenCallback( trackId, section.id, newOpenState );
	};

	return (
		( totalCount > 0 || showDismissed === true ) && (
		<details
			className="nfd-section"
			data-nfd-section-id={ section.id }
			data-nfd-section-index={ index }
			onToggle={ handleToggleOpen }
			open={ section.open }
		>
			<summary className="nfd-section-header">
				<Title className="nfd-section-title mb-0" as="h3">
					<span className="nfd-section-header-icon nfd-header-icon">
						<span className="nfd-section-header-icon-closed">
							{ plusCircleIcon }
						</span>
						<span className="nfd-section-header-icon-opened">
							{ minusCircleIcon }
						</span>
					</span>
					{ section.label }
				</Title>
				{ totalCount > 0 && <ProgressBar completed={ completedCount } total={ totalCount } /> }
			</summary>
			<div className="nfd-section-steps">
				{ section.tasks.map( ( task, taskIndex ) => (
					<ErrorBoundary 
						key={ `task-boundary-${task.id}` }
						fallback={ 
							<div className="nfd-task-error">
								<p>{ __('Task temporarily unavailable', 'wp-module-next-steps') }</p>
							</div> 
						}
					>
						<Task
							index={ taskIndex }
							key={ task.id }
							sectionId={ section.id }
							showDismissed={ showDismissed }
							task={ task }
							taskUpdateCallback={ taskUpdateCallback }
							trackId={ trackId }
						/>
					</ErrorBoundary>
				) ) }
			</div>
			<div
				className="nfd-section-complete"
				data-complete={ isComplete }
				data-show-celebration={ showCompleteCelebration }
				onClick={ ( e ) => {
					setShowCompleteCelebration( false );
					sectionOpenCallback( trackId, section.id, false );
				} }
			>
				<button
					className="nfd-nextsteps-section-close-button"
					title={ __( 'Close', 'wp-module-next-steps' ) }
				>
					{ closeCircleIcon }
				</button>
				<div className="nfd-section-celebrate">{ trophyIcon }</div>
				<p className="nfd-section-celebrate-text">{ __( 'All complete!', 'wp-module-next-steps' ) }</p>
			</div>

		</details>
		)
	);
});
