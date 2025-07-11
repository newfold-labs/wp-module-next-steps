export const ProgressBar = ( { completed, total } ) => {
	const percent = total ? ( completed / total ) * 100 : 0;
	return (
		<div className="nfd-progress-bar">
			<div
				className="nfd-progress-bar-inner"
				style={ { width: `${ percent }%` } }
			/>
			<span className="nfd-progress-bar-label">
				{ completed }/{ total }
			</span>
		</div>
	);
};
