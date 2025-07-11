import { useState } from '@wordpress/element';
import { ProgressBar } from '../progressBar';
import { openCircle, closeCircle } from '../icons';
import { Task } from '../task';

export const Section = ( { section, index, taskUpdateCallback, track } ) => {
	const [ open, setOpen ] = useState( true ); // Sections open by default

	const completed = section.tasks.filter(
		( task ) => task.status === 'done'
	).length;
	const total = section.tasks.length;

	return (
		<details className="nfd-section">
			<summary className="nfd-section-header">
				<h3 className="mb-0">
					<span className="nfd-section-header-icon">
						<span className="nfd-section-header-icon-closed">
							{ openCircle }
						</span>
						<span className="nfd-section-header-icon-opened">
							{ closeCircle }
						</span>
					</span>
					{ section.label }
				</h3>
				<ProgressBar completed={ completed } total={ total } />
			</summary>
			<div className="nfd-section-steps">
				{ section.tasks.map( ( step ) => (
					<Task
						taskUpdateCallback={ taskUpdateCallback }
						description={ step.description }
						href={ step.href }
						id={ step.id }
						key={ step.id }
						title={ step.title }
						track={ track }
						section={ section.id }
						status={ step.status }
						step={ step }
					/>
				) ) }
			</div>
		</details>
	);
};
