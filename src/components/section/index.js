import { useState } from '@wordpress/element';
import { Title } from '@newfold/ui-component-library';
import { ProgressBar } from '../progressBar';
import { openCircle, closeCircle } from '../icons';
import { Task } from '../task';

export const Section = ( props ) => {
	const {
		section,
		index,
		taskUpdateCallback,
		sectionOpenCallback,
		track,
		showDismissed,
		...restProps
	} = props;

	// Use persisted open state from section data, fallback to passed-in open prop or default for first section
	let isOpen = section.open !== undefined ? section.open : index === 0;
	
	const completed = section.tasks.filter(
		( task ) => task.status === 'done'
	).length;
	const total = section.tasks.filter(
		( task ) => task.status !== 'dismissed'
	).length;
	
	// Auto-close the section if all tasks are completed, but only if it's not explicitly set to open
	if ( completed === total && section.open !== true ) {
		isOpen = false;
	}

	return (
		<details className="nfd-section" open={ isOpen } { ...restProps }>
			<summary
				className="nfd-section-header" 
				onClick={ ( e ) => { sectionOpenCallback( section.id, !isOpen ); } }
			>
				<Title className="nfd-section-title mb-0" as="h3">
					<span className="nfd-section-header-icon nfd-header-icon">
						<span className="nfd-section-header-icon-closed">
							{ openCircle }
						</span>
						<span className="nfd-section-header-icon-opened">
							{ closeCircle }
						</span>
					</span>
					{ section.label }
				</Title>
				{ total > 0 && <ProgressBar completed={ completed } total={ total } /> }
			</summary>
			<div className="nfd-section-steps">
				{ section.tasks.map( ( step ) => (
					<Task
						taskUpdateCallback={ taskUpdateCallback }
						showDismissed={ showDismissed }
						description={ step.description }
						href={ step.href }
						id={ step.id }
						key={ step.id }
						title={ step.title }
						track={ track }
						section={ section.id }
						status={ step.status }
						step={ step }
						data_attributes={ step.data_attributes || {} }
					/>
				) ) }
			</div>
		</details>
	);
};
