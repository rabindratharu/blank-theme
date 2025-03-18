/**
 * Webpack configuration file.
 */

// WordPress webpack config
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

// Plugins
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const CopyPlugin = require('copy-webpack-plugin');
const RtlCssPlugin = require('rtlcss-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

// Utilities
const fs = require( 'fs' );
const path = require('path');
const { globSync } = require('glob');

/**
 * Read all file entries in a directory.
 * @param {string} dir Directory to read.
 * @return {Object} Object with file entries.
 */
const readAllFileEntries = ( dir ) => {
	const entries = {};

	if ( ! fs.existsSync( dir ) ) {
		return entries;
	}

	if ( fs.readdirSync( dir ).length === 0 ) {
		return entries;
	}

	fs.readdirSync( dir ).forEach( ( fileName ) => {
		const fullPath = `${ dir }/${ fileName }`;
		if ( ! fs.lstatSync( fullPath ).isDirectory() && ! fileName.startsWith( '_' ) ) {
			entries[ fileName.replace( /\.[^/.]+$/, '' ) ] = fullPath;
		}
	} );

	return entries;
};

// Environment
const isProduction = process.env.NODE_ENV === 'production';

// Base shared config
const baseConfig = {
  ...defaultConfig,
  entry: {
    'js/index': path.resolve(process.cwd(), 'assets/src/js', 'index.js'),
    'css/public': path.resolve(process.cwd(), 'assets/src/css', 'public.css'),
  },
  output: {
    path: path.resolve(process.cwd(), 'assets/build'),
    filename: '[name].js',
    chunkFilename: '[name].js',
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
            sourceMap: !isProduction,
          },
        },
      },
      {
        test: /\.(sc|sa|c)ss$/,
        exclude: /node_modules/,
        use: [
          MiniCssExtractPlugin.loader,
          { loader: 'css-loader', options: { sourceMap: !isProduction } },
          { loader: 'postcss-loader', options: { sourceMap: !isProduction } },
          { loader: 'sass-loader', options: { sourceMap: !isProduction } },
        ],
      },
    ],
  },
  plugins: [
    ...defaultConfig.plugins.filter((filter) => !(filter instanceof RtlCssPlugin)),
    new MiniCssExtractPlugin({
      filename: '[name].css',
    }),
    new RemoveEmptyScriptsPlugin({
      stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS,
    }),
    new CopyPlugin({
      patterns: [
        { from: './assets/src/fonts', to: './fonts' },
        { from: './assets/src/library', to: './library' },
        { from: './assets/src/media', to: './media' },
      ],
    }),
    new RtlCssPlugin({
      filename: '[name]-rtl.css',
    }),
  ],
  optimization: {
    ...defaultConfig.optimization,
    minimizer: [
      ...defaultConfig.optimization.minimizer,
      new CssMinimizerPlugin({
        minimizerOptions: {
          preset: [
            'default',
            {
              discardComments: { removeAll: true },
              normalizeWhitespace: isProduction,
            },
          ],
        },
      }),
    ],
  },
  performance: {
    maxAssetSize: 512000,
  },
  devtool: isProduction ? false : 'source-map',
  resolve: {
    modules: [
      path.resolve(process.cwd(), 'node_modules'), // Theme's node_modules
      'node_modules', // Fallback
    ],
  },
};

module.exports = [ baseConfig ];