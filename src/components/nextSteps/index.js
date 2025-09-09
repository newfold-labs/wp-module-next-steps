import { Button } from '@newfold/ui-component-library';
import { useState, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { spinner, hideIcon } from '../icons';
import { SectionCard } from '../section-card';
import { NoMoreCards } from '../no-more-cards';
import './styles.scss';
import { Track } from '../track';
import { NextStepsErrorBoundary } from '../ErrorBoundary';
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

	// Calculate progress data on initial load, then updated per-section
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
            type: 'open',
            value: open,
        };

        sectionUpdateWrapper(
            data,
            ( error ) => {
                // console.error( 'Error updating section open state:', error );
            },
            ( response ) => {
                setPlan( prevPlan => updateSectionInPlan( prevPlan, trackId, sectionId, 'open', open ) );
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

    const sectionUpdateCallback = ( trackId, sectionId, status ) => {
        if ( !trackId || !sectionId ) {
            // Could not find track for intendend section
            return;
        }

        const data = {
            plan_id: plan.id,
            track_id: trackId,
            section_id: sectionId,
            type: 'status',
            value: status,
        };

        sectionUpdateWrapper(
            data,
            ( error ) => {
                // console.error( 'Error updating section status state:', error );
            },
            ( response ) => {
                setPlan( prevPlan => updateSectionInPlan( prevPlan, trackId, sectionId, 'status', status ) );
            }
        );
    };


    const renderCards  = ( sectionsAsCards, trackId ) => {
        return (
            <>
                <div id={ 'nfd-quick-add-product-modal-only' }/>
                <div className="nfd-nextsteps nfd-grid nfd-grid-cols-2 nfd-grid-rows-[auto_auto] nfd-gap-4" id="nfd-nextsteps">
                    { sectionsAsCards.slice( 0, 3 ).map( ( sectionsAsCard, i ) => {
                        return <SectionCard
                            className={ i === 2 ? 'nfd-col-span-2 nfd-row-span-1' : 'nfd-col-span-1 nfd-row-span-1' }
                            key={ sectionsAsCard.id }
                            wide={ i === 2 }
                            isPrimary={ i === 0 } // TODO: make this dynamic based on the first non-completed section
                            taskUpdateCallback={ taskUpdateCallback }
                            sectionUpdateCallback = { sectionUpdateCallback }
                            desc={ sectionsAsCard.description }
                            trackId={ trackId }
                            sectionId={ sectionsAsCard.id }
                            { ...sectionsAsCard }
                        />
                    } ) }
                </div>
            </>
        );
    }

	// Handle case where plan might not be loaded yet
	if ( ! planWithProgress || ! planWithProgress.tracks ) {
		return (
			<div className="nfd-nextsteps" id="nfd-nextsteps">
				{ spinner }
				<p>{ __( 'Loading next steps...', 'wp-module-next-steps' ) }</p>
			</div>
		);
	}

    if ( planWithProgress.id === 'store_setup' ) {
        const nowSeconds = Math.floor( Date.now() / 1000 );
        // Filter out done tasks and tasks completed/skipped in the last 24 hours
        const sectionsAsCards = planWithProgress.tracks[0].sections.filter( ( section ) => {
            section.date_now = nowSeconds;
            // if section is completed or skipped and has a date completed
            if ( section.status !== 'new' && section.date_completed ) {
                // check if date completed is in last 24 hours // 1 minute for testing
                const dateDelay = 60; // 24 * 60 * 60;
                // set expiry timestamp to 24 hours from date completed
                const expiryTimestamp = section.date_completed + dateDelay;
                // if now is before expiry timestamp, return false
                if ( nowSeconds < expiryTimestamp ) {
                    return false;
                }
            }
            // if section is not completed or does not have a date completed past expiry timestamp, return true
            return true;
        } );
        // We should have only one track for store setup.
        const trackId = planWithProgress.tracks[0].id;
        // calculate primary task - first section with status !== completed
        

        return (
            <>
                { !sectionsAsCards.length && <NoMoreCards/> }
                { sectionsAsCards && renderCards( sectionsAsCards, trackId ) }
            </>
        )
    }

	return (

		<NextStepsErrorBoundary>
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
		</NextStepsErrorBoundary>
	);
};
