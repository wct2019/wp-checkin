module.exports = {
	entry: './assets/js/app.js',
	output: {
		path: `${__dirname}/public/assets/js`,
		filename: 'app.js'
	},
	mode: 'production',
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /(node_modules)/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: ['@babel/preset-env'],
						plugins: ['@babel/plugin-transform-react-jsx']
					}
				}
			}
		]
	}
}
