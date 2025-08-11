import { useEffect, useState, memo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Title } from '@newfold/ui-component-library';
import { plusCircleIcon, minusCircleIcon, closeCircleIcon,trophyIcon } from '../icons';
import { ProgressBar } from '../progressBar';
import { Task } from '../task';

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

	useEffect( () => {
		if ( isComplete && totalCount > 0 ) {
			// give success celebration a little delay
			const timer = setTimeout(() => {
				setShowCompleteCelebration( true );
			}, 150);
			// Clean up the timer when the component unmounts
			return () => clearTimeout(timer);
		}
	}, [ isComplete, totalCount ] );

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
					<Task
						index={ taskIndex }
						key={ task.id }
						sectionId={ section.id }
						showDismissed={ showDismissed }
						task={ task }
						taskUpdateCallback={ taskUpdateCallback }
						trackId={ trackId }
					/>
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
