import { Modal } from '@newfold/ui-component-library';
import { Task } from '../task';
import classNames from 'classnames';

export const TasksModal = ( {
	isOpen,
	OnClose,
	title = '',
	desc = '',
	tasks,
	className,
	taskUpdateCallback,
	...props
} ) => {
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