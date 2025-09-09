import { Modal } from '@newfold/ui-component-library';
import { Task } from '../task';
import classNames from 'classnames';
import { useEffect, useRef } from 'react';

export const TasksModal = ( {
	isOpen,
	OnClose,
	title = '',
	desc = '',
	tasks,
	className,
	taskUpdateCallback,
	sectionUpdateCallback,
	...props
} ) => {
	// Track if we've already sent the completion callback to prevent endless calls
	const hasCompletedRef = useRef(false);

	// Check if all tasks are completed and auto-complete section
	useEffect(() => {
		if (!tasks || !sectionUpdateCallback || !props.trackId || !props.sectionId) {
			return;
		}

		// Filter out dismissed tasks (they don't count toward completion)
		const activeTasks = tasks.filter(task => task.status !== 'dismissed');
		
		// Check if all active tasks are completed
		const allTasksCompleted = activeTasks.length > 0 && activeTasks.every(task => task.status === 'done');
		
		if (allTasksCompleted && !hasCompletedRef.current) {
			// Mark that we've sent the completion callback
			hasCompletedRef.current = true;
			// Auto-complete the section
			sectionUpdateCallback(props.trackId, props.sectionId, 'completed');
		} else if (!allTasksCompleted) {
			// Reset the completion flag if tasks are no longer all completed
			hasCompletedRef.current = false;
		}
	}, [tasks, sectionUpdateCallback, props.trackId, props.sectionId]);

	return <Modal
		isOpen={ isOpen }
		onClose={ OnClose }
		className={ classNames(
			'nfd-nextstep-tasks-modal',
			className
		) }
		{ ...props }
	>
		<Modal.Panel>
			{
				!! title &&
				<Modal.Title className={ 'nfd-text-xl nfd-font-semibold nfd-mb-6' }>
					{ title }
				</Modal.Title>
			}
			{
				!! desc &&
				<Modal.Description>
					{ desc }
				</Modal.Description>

			}
			<div className={'nfd.nfd-nextstep-tasks-modal__tasks'}>
				{
					tasks.map(( task, taskIndex ) =>(
                    <Task
							key={ task.id }
                            index={ taskIndex }
                            task={ task }
							taskUpdateCallback={ taskUpdateCallback }
							showDismissed={ true }
							trackId={ props?.trackId }
							sectionId={ props?.sectionId }
						/>
					) )
				}
			</div>
		</Modal.Panel>
	</Modal>
}