/**
 * Helper functions for API calls and plan state immutability
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Calculate progress data for all sections in a plan
 * @param {Object} plan - The plan data
 * @returns {Object} Plan with progress data added to each section
 */
export const calculatePlanProgress = (plan) => {
	return {
		...plan,
		tracks: plan.tracks.map(track => ({
			...track,
			sections: track.sections.map(section => {
				const totalCount = section.tasks.filter(t => t.status !== 'dismissed').length;
				const completedCount = section.tasks.filter(t => t.status === 'done').length;
				
				return {
					...section,
					progress: {
						totalCount,
						completedCount,
						isComplete: totalCount > 0 && completedCount === totalCount,
						percentage: totalCount > 0 ? Math.round((completedCount / totalCount) * 100) : 0
					}
				};
			})
		}))
	};
};

/**
 * Update task status in plan state immutably
 * @param {Object} plan - The current plan state
 * @param {string} trackId - Track ID
 * @param {string} sectionId - Section ID  
 * @param {string} taskId - Task ID
 * @param {string} newStatus - New task status
 * @returns {Object} New plan state with updated task
 */
export const updateTaskStatusInPlan = (plan, trackId, sectionId, taskId, newStatus) => {
	return {
		...plan,
		tracks: plan.tracks.map(track => 
			track.id === trackId 
				? {
					...track,
					sections: track.sections.map(section =>
						section.id === sectionId
							? {
								...section,
								tasks: section.tasks.map(task =>
									task.id === taskId
										? { ...task, status: newStatus }
										: task
								)
							}
							: section
					)
				}
				: track
		)
	};
};

/**
 * Update section open state in plan state immutably
 * @param {Object} plan - The current plan state
 * @param {string} trackId - Track ID
 * @param {string} sectionId - Section ID
 * @param {boolean} isOpen - New open state
 * @returns {Object} New plan state with updated section
 */
export const updateSectionInPlan = (plan, trackId, sectionId, isOpen) => {
	return {
		...plan,
		tracks: plan.tracks.map(track => 
			track.id === trackId 
				? {
					...track,
					sections: track.sections.map(section =>
						section.id === sectionId
							? { ...section, open: isOpen }
							: section
					)
				}
				: track
		)
	};
};

/**
 * Update track open state in plan state immutably
 * @param {Object} plan - The current plan state
 * @param {string} trackId - Track ID
 * @param {boolean} isOpen - New open state
 * @returns {Object} New plan state with updated track
 */
export const updateTrackInPlan = (plan, trackId, isOpen) => {
	return {
		...plan,
		tracks: plan.tracks.map(track => 
			track.id === trackId 
				? { ...track, open: isOpen }
				: track
		)
	};
};

/**
 * Method to create endpoint url
 * 
 * no permalinks: 'http://localhost:8882/index.php?rest_route=/'
 * permalinks: 'http://localhost:8882/wp-json/'
 */
export const createEndpointUrl = ( root, endpoint ) => {
	// if restUrl has /index.php?rest_route=/, add escaped endpoint
	if ( root.includes( '?' ) ) {
		return root + encodeURIComponent( endpoint );
	} 
	// otherwise permalinks set and restUrl should concatenate endpoint
	return root + endpoint;
};

/**
 * Wrapper method to post task update to endpoint
 *
 * @param {Object}   data         object of data
 * @param {Function} passError    method to handle the error in component
 * @param {Function} thenCallback method to call in promise then
 */
export const taskUpdateWrapper = ( data, passError, thenCallback ) => {
	return apiFetch( {
		url: createEndpointUrl(
			window.NewfoldRuntime.restUrl,
			'newfold-next-steps/v1/steps/status'
		),
		method: 'PUT',
		data,
	} )
		.then( ( response ) => {
			thenCallback( response );
		} )
		.catch( ( error ) => {
			passError( error );
		} );
};

/**
* Wrapper method to post section update to endpoint
*
* @param {Object}   data         object of data
* @param {Function} passError    method to handle the error in component
* @param {Function} thenCallback method to call in promise then
*/
export const sectionUpdateWrapper = ( data, passError, thenCallback ) => {
	return apiFetch( {
		url: createEndpointUrl( 
			window.NewfoldRuntime.restUrl, 
			'newfold-next-steps/v1/steps/section/open'
		),
		method: 'PUT',
		data,
	} )
		.then( ( response ) => {
			thenCallback( response );
		} )
		.catch( ( error ) => {
			passError( error );
		} );
};

/**
* Wrapper method to post track update to endpoint
*
* @param {Object}   data         object of data
* @param {Function} passError    method to handle the error in component
* @param {Function} thenCallback method to call in promise then
*/
export const trackUpdateWrapper = ( data, passError, thenCallback ) => {
	return apiFetch( {
		url: createEndpointUrl( 
			window.NewfoldRuntime.restUrl, 
			'newfold-next-steps/v1/steps/track/open'
		),
		method: 'PUT',
		data,
	} )
		.then( ( response ) => {
			thenCallback( response );
		} )
		.catch( ( error ) => {
			passError( error );
		} );
};
