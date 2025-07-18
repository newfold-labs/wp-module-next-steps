export const ProgressBar = ( { completed, total } ) => {
	const percent = total ? Math.round( ( completed / total ) * 100 ) : 0;
	return (
		<div className={`nfd-progress-bar nfd-progress-bar-${ percent }`}>
			<div
				className="nfd-progress-bar-inner"
				data-percent={ percent }
				style={ { width: `${ percent }%` } }
			/>
			<span className="nfd-progress-bar-label">
				{ completed }/{ total }
			</span>
		</div>
	);
};
