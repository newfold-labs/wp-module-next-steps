import { noMoreCardsicon } from './wireframes';
import {Title} from "@newfold/ui-component-library";
import { __ } from '@wordpress/i18n';

export const NoMoreCards = ( {

} ) => {

	return (
		<>
            <div className={'nfd-nextsteps-step-content'}>
                <Title size={ 2 } as="h3" className="nfd-mb-4">
                    { __( 'Pending tasks', 'wp-module-next-steps' ) }
                </Title>

                <div className= { 'ndf-nextsteps-no-cards-content nfd-items-center nfd-flex nfd-flex-col nfd-justify-between' } >
                    <div className={ 'nfd-nextsteps-step-card__wireframe' }>
                        { noMoreCardsicon }
                    </div>
                    <Title size={ 3 } as="span" className="nfd-mb-1">
                        { __( 'Hooray!', 'wp-module-next-steps' ) }
                    </Title>
                    <Title size={ 5 } as="span" className="nfd-text-center nfd-next-steps-no-cards-text">
                        { __( 'You don’t have any pending task today', 'wp-module-next-steps' )  }
                    </Title>
                </div>
            </div>
		</>
	);
};
