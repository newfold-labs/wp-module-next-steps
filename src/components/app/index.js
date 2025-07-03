import { Page } from '@newfold/ui-component-library';
import { NextSteps } from '../nextSteps';
import classNames from 'classnames';

export const NextStepsApp = () => {
	const classes = classNames( 'nfd-next-steps-app-container' );

	return (
		<Page className={ classes }>
			<NextSteps />
		</Page>
	);
};
