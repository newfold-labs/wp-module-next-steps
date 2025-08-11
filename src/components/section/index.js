import { useEffect, useState, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Title } from '@newfold/ui-component-library';
import { plusCircleIcon, minusCircleIcon, closeCircleIcon,trophyIcon } from '../icons';
import { ProgressBar } from '../progressBar';
import { Task } from '../task';

export const Section = ( props ) => {
	const {
		index,
		section,
		sectionOpenCallback,
		showDismissed,
		taskUpdateCallback,
		trackId,
	} = props;
	
	const [ totalCount, setTotalCount ] = useState( 0 );
	const [ completedCount, setCompletedCount ] = useState( 0 );
	const [ showCompleteCelebration, setShowCompleteCelebration ] = useState( false );
	const [ isComplete, setIsComplete ] = useState( false );
	// Use persisted open state from section data, fallback to passed-in open prop or default for first section
	const initialOpenState = section.open;
	const detailsRef = useRef( null );
	const isInitialized = useRef( false );

	const init = () => {
		calculateCounts();
	};
	// Calculate total task count
	const calculateCounts = () => {
		setTotalCount( getTotalCount() );
		setCompletedCount( getCompletedCount() );
	};
	const getTotalCount = () => {
		return section.tasks.filter( ( task ) => task.status !== 'dismissed' ).length;
	};
	const getCompletedCount = () => {
		return section.tasks.filter( ( task ) => task.status === 'done' ).length;
	};

	// Wrapper for taskUpdateCallback that updates counts after task status changes
	const sectionTaskUpdateCallback = ( trackId, sectionId, taskId, status, errorCallback = () => {}, successCallback = () => {} ) => {
		taskUpdateCallback( trackId, sectionId, taskId, status, (error) => {
			// Update the counts after failed task update - most likely redundant
			calculateCounts();
			errorCallback( error );
		}, ( response ) => {
			setIsComplete( false );
			successCallback( response );
			// Task status updated in wrapper callback
			// Update the counts after successful task update
			calculateCounts();
			setShowCompleteCelebration( true );
		} );
	};

	// on mount, initialize the counts and set initial open state
	useEffect( () => {
		init();
		// Set initial open state imperatively without triggering callbacks
		if ( detailsRef.current ) {
			detailsRef.current.open = initialOpenState;
		}
		// Use setTimeout to ensure initialization happens after any triggered events
		setTimeout( () => {
			isInitialized.current = true;
		}, 0 );
	}, [] );

	useEffect( () => {
		if ( completedCount === totalCount ) {
			// give success celebration a little delay
			const timer = setTimeout(() => {
				setIsComplete( true );
			}, 150);
			// Clean up the timer when the component unmounts
			return () => clearTimeout(timer);
		}
	}, [ completedCount, totalCount, section.tasks ] );

	const handleToggleOpen = ( event ) => {
		// Prevent event from bubbling up to parent track details element
		event.stopPropagation();
		
		// Only call the callback if this is a user-triggered event (after initialization)
		if ( ! isInitialized.current ) {
			return;
		}
		
		// Get the new open state from the details element
		const newOpenState = event.target.open;
		// Call the callback to update the backend
		sectionOpenCallback( trackId, section.id, newOpenState );
	};

	return (
		( totalCount > 0 || showDismissed === true )&& (
		<details
			className="nfd-section"
			data-nfd-section-id={ section.id }
			data-nfd-section-index={ index }
			onToggle={ handleToggleOpen }
			ref={ detailsRef }
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
						taskUpdateCallback={ sectionTaskUpdateCallback }
						trackId={ trackId }
					/>
				) ) }
			</div>
			{ showCompleteCelebration && 
				<div
					className="nfd-section-complete"
					data-complete={ isComplete }
					onClick={ ( e ) => {
						setShowCompleteCelebration( false );
						// Programmatically close the details element
						if (detailsRef.current) {
							detailsRef.current.open = false;
						}
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
			}
		</details>
		)
	);
};
