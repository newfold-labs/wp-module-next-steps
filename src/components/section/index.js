import ProgressBar from './ProgressBar';
import Step from './Step';

const Section = ( { section } ) => {
	const [ open, setOpen ] = useState( true ); // Sections open by default

	const completed = section.tasks.filter(
		( task ) => task.status === 'complete'
	).length;
	const total = section.tasks.length;

	return (
		<div className="nfd-section">
			<div
				className="nfd-section-header"
				onClick={ () => setOpen( ! open ) }
			>
				<h3>{ section.label }</h3>
				<ProgressBar completed={ completed } total={ total } />
				<span>{ open ? '−' : '+' }</span>
			</div>
			{ open && (
				<ul className="nfd-section-steps">
					{ section.tasks.map( ( step ) => (
						<Step key={ step.id } step={ step } />
					) ) }
				</ul>
			) }
		</div>
	);
};

export default Section;
