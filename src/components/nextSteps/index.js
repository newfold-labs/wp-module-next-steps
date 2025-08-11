import { useState, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button } from '@newfold/ui-component-library';
import { spinner, hideIcon } from '../icons';
import { Track } from '../track';
import { 
	calculatePlanProgress,
	updateTaskStatusInPlan,
	updateSectionInPlan,
	updateTrackInPlan,
	taskUpdateWrapper,
	sectionUpdateWrapper,
	trackUpdateWrapper
} from './helpers';
import './styles.scss';


export const NextSteps = () => {
	const [ plan, setPlan ] = useState( window.NewfoldNextSteps );
	const [ showDismissed, setShowDismissed ] = useState( true );
	const [ showControls, setShowControls ] = useState( false );

	// Calculate progress data (internally memoized based on task statuses)
	const planWithProgress = useMemo(() => {
		return plan ? calculatePlanProgress(plan) : null;
	}, [plan]);

	const taskUpdateCallback = ( trackId, sectionId, taskId, status, errorCallback, successCallback ) => {
		// send update to endpoint
		const data = {
			plan_id: plan.id,
			track_id: trackId,
			section_id: sectionId,
			task_id: taskId,
			status,
		};
		taskUpdateWrapper(
			data,
			( error ) => {
				errorCallback( error );
			},
			( response ) => {
				// update plan state with the new task status using immutability helper
				setPlan( prevPlan => updateTaskStatusInPlan( prevPlan, trackId, sectionId, taskId, status ) );
				// call provided success callback
				successCallback( response );
			}
		);
	};

	const sectionOpenCallback = ( trackId, sectionId, open ) => {		
		if ( !trackId || !sectionId ) {
			// Could not find track for intendend section
			return;
		}

		const data = {
			plan_id: plan.id,
			track_id: trackId,
			section_id: sectionId,
			open: open,
		};
		
		sectionUpdateWrapper( 
			data,
			( error ) => {
				// console.error( 'Error updating section open state:', error );
			},
			( response ) => {
				setPlan( prevPlan => updateSectionInPlan( prevPlan, trackId, sectionId, open ) );
			}
		);
	};

	const trackOpenCallback = ( trackId, open ) => {
		const data = {
			plan_id: plan.id,
			track_id: trackId,
			open: open,
		};
		
		trackUpdateWrapper( 
			data,
			( error ) => {
				// console.error( 'Error updating track open state:', error );
			},
			( response ) => {
				setPlan( prevPlan => updateTrackInPlan( prevPlan, trackId, open ) );
			}
		);
	};

	// Handle case where plan might not be loaded yet
	if ( ! planWithProgress || ! planWithProgress.tracks ) {
		return (
			<div className="nfd-nextsteps" id="nfd-nextsteps">
				{ spinner }
				<p>{ __( 'Loading next steps...', 'wp-module-next-steps' ) }</p>
			</div>
		);
	}

	return (
		<div
			className="nfd-nextsteps"
			data-nfd-plan-id={ planWithProgress.id }
			id="nfd-nextsteps"
		>
			<p className="nfd-pb-4">{ planWithProgress.description }</p>
			{ planWithProgress.tracks.map( ( track, trackIndex ) => (
				<Track
					index={ trackIndex }
					key={ track.id }
					sectionOpenCallback={ sectionOpenCallback }
					showDismissed={ showDismissed }
					taskUpdateCallback={ taskUpdateCallback }
					track={ track }
					trackOpenCallback={ trackOpenCallback }
				/>
			) ) }
			{ showControls && <div className="nfd-nextsteps-filters nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-center">
				<Button
					className="nfd-nextsteps-filter-button"
					data-nfd-click="nextsteps_step_toggle"
					data-nfd-event-category="nextsteps_toggle"
					data-nfd-event-key="toggle"
					onClick={ () => {
						setShowDismissed( ! showDismissed );
					} }
					variant="secondary"
				>{ hideIcon }
					{ showDismissed
						? __( 'Hide skipped tasks', 'wp-module-next-steps' )
						: __( 'View skipped tasks', 'wp-module-next-steps' )
					}
				</Button>
			</div> }
		</div>
	);
};
