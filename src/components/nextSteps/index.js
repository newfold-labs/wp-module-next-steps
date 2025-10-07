import { Button } from '@newfold/ui-component-library';
import { useState, useMemo } from '@wordpress/element';
import classnames from 'classnames';
import { getDate, humanTimeDiff, format, dateI18n } from '@wordpress/date';
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
	taskUpdateWrapper,
	sectionUpdateWrapper,
	trackUpdateWrapper
} from '../../utils/helpers';
import './styles.scss';
import { useViewportMatch } from '@wordpress/compose';

export const NextSteps = () => {
	const [ plan, setPlan ] = useState( window.NewfoldNextSteps );
	const [ showDismissed, setShowDismissed ] = useState( true );
	const [ showControls, setShowControls ] = useState( false );

	// Calculate progress data on initial load, then updated per-section
	const planWithProgress = useMemo( () => {
		return plan ? calculatePlanProgress( plan ) : null;
	}, [ plan ] );

	const taskUpdateCallback = ( trackId, sectionId, taskId, status, errorCallback = () => {}, successCallback = () => {} ) => {
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
				// update plan state with the response data from API
				if ( response && response.id && response.status ) {
					setPlan( prevPlan => updateTaskStatusInPlan( prevPlan, trackId, sectionId, taskId, response.status ) );
				} else {
					// fallback to local status if response is invalid
					setPlan( prevPlan => updateTaskStatusInPlan( prevPlan, trackId, sectionId, taskId, status ) );
				}
				// call provided success callback
				successCallback( response );
			}
		);
	};

	const sectionOpenCallback = ( trackId, sectionId, open ) => {
		if ( ! trackId || ! sectionId ) {
			// Could not find track for intended section
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
				// Use the returned section data to update the plan state
				if ( response && typeof response === 'object' ) {
					setPlan( prevPlan => {
						return {
							...prevPlan,
							tracks: prevPlan.tracks.map( track =>
								track.id === trackId
									? {
										...track,
										sections: track.sections.map( section =>
											section.id === sectionId
												? { ...section, ...response }
												: section
										)
									}
									: track
							)
						};
					} );
				}
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
				// Use the returned track data to update the plan state
				if ( response && typeof response === 'object' ) {
					setPlan( prevPlan => {
						return {
							...prevPlan,
							tracks: prevPlan.tracks.map( track =>
								track.id === trackId
									? { ...track, ...response }
									: track
							)
						};
					} );
				}
			}
		);
	};

	const sectionUpdateCallback = ( trackId, sectionId, status, errorCallback = () => {}, successCallback = () => {} ) => {
		if ( ! trackId || ! sectionId ) {
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
				errorCallback( error );
			},
			( response ) => {
				// Use the returned section data to update the plan state
				if ( response && typeof response === 'object' ) {
					setPlan( prevPlan => {
						return {
							...prevPlan,
							tracks: prevPlan.tracks.map( track =>
								track.id === trackId
									? {
										...track,
										sections: track.sections.map( section =>
											section.id === sectionId
												? { ...section, ...response }
												: section
										)
									}
									: track
							)
						};
					} );
				}
				// call provided success callback
				successCallback( response );
			}
		);
	};


	const renderCards = ( sectionsAsCards, trackId ) => {
		const isLargeViewport = useViewportMatch( 'medium' );
		let maxCards = 3;
		// check url for showallsteps query parameter that is true
		const showAllSteps = new URLSearchParams( window.location.search ).get( 'showallsteps' ) === 'true';
		if ( showAllSteps ) {
			maxCards = sectionsAsCards.length;
		}
		return (
			<>
				<div id={ 'nfd-quick-add-product-modal' }/>
				<div className={ classnames(
					'nfd-nextsteps nfd-grid nfd-gap-4',
					{
						'nfd-nextsteps--single-column': ! isLargeViewport,
					}
				) }
					id="nfd-nextsteps">
					{ sectionsAsCards.slice( 0, maxCards ).map( ( sectionsAsCard, i ) => {
						return <SectionCard
							key={ sectionsAsCard.id }
							wide={ i % 3 == 2 && isLargeViewport }
							isPrimary={ sectionsAsCard.isPrimary === true } // calculated in filter to determine first new section
							taskUpdateCallback={ taskUpdateCallback }
							sectionUpdateCallback={ sectionUpdateCallback }
							desc={ sectionsAsCard.description }
							trackId={ trackId }
							sectionId={ sectionsAsCard.id }
							index={ i }
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
		const now = new Date();
		const nowDate = dateI18n( 'Y-m-d H:i:s', now );
		let hasPrimary = false; // track isPrimary flag
		// Filter out done tasks and tasks completed/skipped in the last 24 hours
		const sectionsAsCards = planWithProgress.tracks[ 0 ].sections.filter( ( section ) => {
			// if section is done or skipped and has a date completed
			if ( section.status !== 'new' && section.date_completed ) {
                if( section.completed_by === 'system' ) {
                    return false; // hide system completed tasks
                }
				// check if date completed is in last 24 hours
				const completedDate = getDate( section.date_completed );
				const expiryOffset = 24 * 60 * 60 * 1000; // 24 hours in milliseconds
				const expiryDateObj = new Date( completedDate.getTime() + expiryOffset );
				const expiryDate = dateI18n( 'Y-m-d H:i:s', expiryDateObj );
				// determine if now is after expiry date and set to hide section if so
				const shouldHide = now > expiryDateObj;
				// save date values to section for use in section-card component/debugging
				section.expiresIn = humanTimeDiff( expiryDate, nowDate );
				section.expiryDate = format( 'Y-m-d H:i:s', expiryDate );
				section.nowDate = format( 'Y-m-d H:i:s', nowDate );

				// if not expired yet, return false (hide the section)
				if ( shouldHide ) {
					return false;
				}
			}
			// if status is not new and no date completed, return false - this is legacy data from earlier version
			if ( section.status !== 'new' && ! section.date_completed ) {
				// this avoids rendering sections that a user completed before date_completed tracking began
				return false;
			}
			// calculate primary task - first section with status === new
			if ( section.status === 'new' && ! hasPrimary ) {
				section.isPrimary = true;
				hasPrimary = true;
			}
			// if section is not completed or does not have a date completed past expiry timestamp, return true
			return true;
		} );
		// We should have only one track for store setup.
		const trackId = planWithProgress.tracks[ 0 ].id;


		return (
			<>
				{ ! sectionsAsCards.length && <NoMoreCards/> }
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
						sectionUpdateCallback={ sectionUpdateCallback }
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
