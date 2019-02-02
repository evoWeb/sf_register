const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const path = require('path');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

let entries = {};
entries['sf_register'] = entries['sf_register.min'] = [
	'./Private/Scripts/SfRegister'
];
entries['styles'] = entries['styles.min'] = [
	'./Private/Scss/styles.scss'
];

module.exports = {
	entry: entries,
	devtool: 'source-map',
	output: {
		path: path.resolve(__dirname),
		filename: './Public/JavaScript/[name].js'
	},
	optimization: {
		minimize: true,
		minimizer: [
			new UglifyJsPlugin({
				include: /\.min\.js$/
			})
		]
	},
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

			{ // sass / scss loader for webpack
				test: /\.(css|sass|scss)$/,
				use: ExtractTextPlugin.extract({
					use: ['css-loader', 'sass-loader'],
				})
			}
		]
	},
	plugins: [
		new ExtractTextPlugin({ // define where to save the file
			filename: './Public/Stylesheets/[name].css',
			allChunks: true,
		}),
	],
	// only to satisfy dependencies of loaded libraries
	externals: {
		jquery: 'jQuery'
	}
};
