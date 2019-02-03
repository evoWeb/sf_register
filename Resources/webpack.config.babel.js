import UglifyJsPlugin from 'uglifyjs-webpack-plugin';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import OptimizeCSSAssetsPlugin from 'optimize-css-assets-webpack-plugin';
import path from 'path';

let entries = {};
entries['sf_register'] = entries['sf_register.min'] = [
	'./Private/Scripts/SfRegister',
	'./Private/Scss/styles.scss'
];

module.exports = {
	entry: entries,
	output: {
		path: path.resolve(__dirname),
		filename: './Public/JavaScript/[name].js'
	},
	devtool: 'source-map',
	module: {
		rules: [
			{
				test: /\.js?/,
				include: [
					path.resolve(__dirname, 'Private')
				],
				loader: 'babel-loader',
				options: {
					presets: [
						'@babel/env'
					],
					'plugins': [
						'@babel/plugin-proposal-class-properties'
					]
				}
			},

			{
				test: /\.(css|sass|scss)$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader
					},
					{
						loader: 'css-loader'
					},
					{
						loader: 'sass-loader'
					}
				]
			}
		]
	},
	optimization: {
		minimize: true,
		minimizer: [
			new UglifyJsPlugin({
				include: /\.min\.js$/,
				cache: true,
				parallel: true,
				sourceMap: true // set to true if you want JS source maps
			}),
			new OptimizeCSSAssetsPlugin({
				assetNameRegExp: /\.min\.css$/,
				sourceMap: true,
				cssProcessorOptions: {
					map: {
						inline: false
					}
				}
			})
		]
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: './Public/Stylesheets/[name].css'
		})
	]
};
