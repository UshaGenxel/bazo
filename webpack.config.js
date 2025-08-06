const path = require('path');
const glob = require('glob');
const fs = require('fs');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');

const entries = {};
const copyPatterns = [];

// ✅ Add global index.js (for Bootstrap, etc.)
const globalEntryPath = path.resolve(__dirname, 'blocks', 'index.js');
if (fs.existsSync(globalEntryPath)) {
	entries.global = globalEntryPath;
}

// ✅ Loop over block folders: blocks/**/index.js, skip the top-level global index.js
glob.sync('./blocks/**/index.js').forEach((file) => {
	const parts = file.split('/');
	const blockName = parts[2];

	// Skip global blocks/index.js
	if (blockName === 'index') return;

	entries[blockName] = file;

	const blockDir = path.resolve(__dirname, 'blocks', blockName);

	// Copy block.json if exists
	const blockJson = path.join(blockDir, 'block.json');
	if (fs.existsSync(blockJson)) {
		copyPatterns.push({
			from: blockJson,
			to: path.resolve(__dirname, 'build', blockName, 'block.json')
		});
	}

	// Copy render.php, editor.css if exist
	['render.php', 'editor.css', 'view.js', 'style.css'].forEach((fileName) => {
		const filePath = path.join(blockDir, fileName);
		if (fs.existsSync(filePath)) {
			copyPatterns.push({
				from: filePath,
				to: path.resolve(__dirname, 'build', blockName, fileName)
			});
		}
	});
});

// Plugins
const plugins = [
	new MiniCssExtractPlugin({
		filename: '[name]/style.css',
	}),
	new DependencyExtractionWebpackPlugin({
		outputFormat: 'php',
		injectPolyfill: true,
	}),
];

if (copyPatterns.length > 0) {
	plugins.push(new CopyWebpackPlugin({ patterns: copyPatterns }));
}

module.exports = {
	entry: entries,

	output: {
		path: path.resolve(__dirname, 'build'),
		filename: '[name]/index.js',
	},

	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: 'babel-loader',
			},
			{
				test: /\.s?css$/,
				use: [
					MiniCssExtractPlugin.loader,
					'css-loader',
					'sass-loader',
				],
			},
			{
				test: /\.(png|jpe?g|gif|svg)$/i,
				type: 'asset/resource',
				generator: {
					filename: 'images/[name][ext]',
				},
			},
			{
				test: /\.(mp4|webm|ogg)$/,
				use: {
					loader: 'file-loader',
					options: {
						name: '[name].[hash].[ext]',
						outputPath: 'videos',
					},
				},
			},
		],
	},

	plugins,

	externals: {
		react: 'React',
		'react-dom': 'ReactDOM',
	},

	resolve: {
		extensions: ['.js', '.jsx'],
	},
};
