import { useEffect, useState } from '@wordpress/element';
import { Title } from '@newfold/ui-component-library';
import { __ } from '@wordpress/i18n';
import { ProgressBar } from '../progressBar';
import { plusCircleIcon, minusCircleIcon, closeCircleIcon,trophyIcon } from '../icons';
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
	
	const [ showCompleteCelebration, setShowCompleteCelebration ] = useState( true );
	const [ isComplete, setIsComplete ] = useState( false );
	// Use persisted open state from section data, fallback to passed-in open prop or default for first section
	const [ isOpen, setIsOpen ] = useState( section.open !== undefined ? section.open : index === 0 );

	const completed = section.tasks.filter(
		( task ) => task.status === 'done'
	).length;
	const total = section.tasks.filter(
		( task ) => task.status !== 'dismissed'
	).length;

	// if section complete on load, don't show complete celebration
	useEffect( () => {
		if ( completed === total ) {
			setShowCompleteCelebration( false );
		}
	}, [] );

	useEffect( () => {
		if ( total === completed ) {
			const timer = setTimeout(() => {
				setIsComplete( !isComplete );
			}, 100);
			// Clean up the timer when the component unmounts
			return () => clearTimeout(timer);
		}
	}, [ showCompleteCelebration, completed, total ] );

	const handleToggleOpen = ( event, state = null ) => {
		// Get the new open state from the details element
		const newOpenState = state !== null ? state : event.target.open;
		// Call the callback to update the backend
		sectionOpenCallback( section.id, newOpenState );
		setIsOpen( newOpenState );
	};

	return (
		( total > 0 || showDismissed === true )&& (
		<details
			className="nfd-section"
			open={ isOpen }
			onToggle={ handleToggleOpen }
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
				{ total > 0 && <ProgressBar completed={ completed } total={ total } /> }
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
						handleToggleOpen( e, false );
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
