const path = require( 'path' );
const { merge } = require( 'webpack-merge' );
const wpScriptsConfig = require( '@wordpress/scripts/config/webpack.config' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );

const apps = [ 'next-steps-portal', 'next-steps-widget' ];

module.exports = apps.map( ( app ) =>
	merge( wpScriptsConfig, {
		entry: {
			[ app ]: path.resolve( __dirname, `./src/${ app }/index.js` ),
		},
		output: {
			path: path.resolve( __dirname, `./build/${ app }` ),
			filename: 'bundle.js',
		},
		module: {
			rules: [
				{
					test: /\.css$/,
					include: [ path.resolve( __dirname, `src/${ app }` ) ],
					use: [ MiniCssExtractPlugin.loader, 'css-loader' ],
				},
				{
					test: /\.svg$/i,
					use: [
						{
							loader: '@svgr/webpack',
							options: {
								svgo: true,
								svgoConfig: {
									plugins: [
										{ name: 'preset-default' },
										{
											name: 'prefixIds',
											params: {
												delim: '-',
												prefix( node, info ) {
													const p = info?.path;
													const baseRaw = p?.basename || p?.path?.split( /[\\/]/ ).pop() || 'svg';
													const base = baseRaw.replace( /\.[^/.]+$/, '' );
													const cls = node?.attributes?.class ? String( node.attributes.class ).trim().split( /\s+/ )[ 0 ] : null;

													return cls ? `${ base }__${ cls }` : `${ base }`;
												},
											},
										},
										{ name: 'inlineStyles', params: { onlyMatchedOnce: false } },
									],
								},
							},
						},
					],
				}
			],
		},
		plugins: [
			new MiniCssExtractPlugin( {
				filename: '[name].css',
			} ),
		],
	} )
);
