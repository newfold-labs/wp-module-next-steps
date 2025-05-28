import { Button, Checkbox } from '@newfold/ui-component-library';
import classNames from 'classnames';
import { __, sprintf } from '@wordpress/i18n';

export const Step = ( {
	id,
	description = '',
	title = '',
	status,
	href,
	completeCallback,
} ) => {
	const getHref = () => {
		// replace {siteUrl} placeholder with the actual site URL
		if ( href.includes( '{siteUrl}' ) ) {
			return href.replace( '{siteUrl}', window.NewfoldRuntime.siteUrl );
		}
		return href;
	};
	const getTarget = () => {
		// if href is external, return target="_blank"
		if (
			href.includes( '{siteUrl}' ) ||
			href.includes( window.NewfoldRuntime.siteUrl )
		) {
			return '';
		}
		return '_blank';
	};
	const stepDoneRender = () => {
		return (
			<div className="nfd-nextsteps-step nfd-nextsteps-step-done">
				<Checkbox
					className="nfd-nextsteps-step-checkbox"
					description={ description }
					id={ id }
					label={ title }
					name={ id }
					value={ id }
					onChange={ ( e ) => completeCallback( id, 'new' ) }
					checked={ true }
					data-nfd-click="nextsteps_step_checkbox"
					data-nfd-event-key={ id }
					data-nfd-event-category="nextsteps_step"
				/>
			</div>
		);
	};
	const stepNewRender = () => {
		return (
			<div className="nfd-nextsteps-step nfd-nextsteps-step-new nfd-flex nfd-flex-row nfd-justify-between nfd-items-center">
				<Checkbox
					className="nfd-nextsteps-step-checkbox"
					id={ id }
					label={ title }
					name={ id }
					value={ id }
					onChange={ ( e ) => completeCallback( id, 'done' ) }
					data-nfd-click="nextsteps_step_checkbox"
					data-nfd-event-key={ id }
					data-nfd-event-category="nextsteps_step"
				/>
				<div className="nfd-nextsteps-buttons">
					<Button
						as="a"
						className="nfd-nextsteps-step-link"
						href={ getHref() }
						size="small"
						target={ getTarget() }
						variant="secondary"
						data-nfd-click="nextsteps_step_link"
						data-nfd-event-key={ id }
						data-nfd-event-category="nextsteps_step"
						title={ description }
					>
						{ __( 'Go', 'wp-module-next-steps' ) }
					</Button>
					<Button
						className="nfd-nextsteps-step-dismiss"
						size="small"
						variant="secondary"
						data-nfd-click="nextsteps_step_dismiss"
						data-nfd-event-key={ id }
						data-nfd-event-category="nextsteps_step"
						onClick={ ( e ) => completeCallback( id, 'dismissed' ) }
					>
						{ __( 'x', 'wp-module-next-steps' ) }
					</Button>
				</div>
			</div>
		);
	};
	const stepDismissedRender = () => {
		return (
			<div className="nfd-nextsteps-step nfd-nextsteps-step-dismissed">
				<Checkbox
					className="nfd-nextsteps-step-checkbox"
					description={ description }
					id={ id }
					label={ title }
					name={ id }
					value={ id }
					onChange={ ( e ) => completeCallback( id, 'new' ) }
					data-nfd-click="nextsteps_step_checkbox"
					data-nfd-event-key={ id }
					data-nfd-event-category="nextsteps_step"
				/>
			</div>
		);
	};

	return (
		<div className="nfd-nextsteps-step" id={ id }>
			{ status === 'done' && stepDoneRender() }
			{ status === 'new' && stepNewRender() }
			{ status === 'dismissed' && stepDismissedRender() }
		</div>
	);
};
