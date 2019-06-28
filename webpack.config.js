'use strict';

const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const ImageminWebpackPlugin = require('imagemin-webpack-plugin').default;

const input = path.resolve(__dirname, './web/app/themes/outlandish');
const output = path.resolve(__dirname, './web/app/themes/outlandish/public');

module.exports = {
  // NODE_ENV set by cross-env in npm scripts
  mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',

  target: 'web',

  context: __dirname,

  entry: {
    admin: path.resolve(input, 'assets/admin.js'),
    app: path.resolve(input, 'assets/app.js')
  },

  externals: {
    jquery: 'jQuery'
  },

  output: {
    path: output,
    publicPath: '/app/themes/outlandish/public/',
    filename: '[name].js'
  },

  devtool: process.env.NODE_ENV === 'development' ? 'source-map' : false,

  module: {
    rules: [
      /**
       * ## babel-loader
       * Compiles down ES6+ JavaScript syntax.
       */
      {
        loader: 'babel-loader',
        test: /\.js$/,
        exclude: /node_modules/,
        options: require('./babel.config')
      },
      {
        /**
         * These loaders allow webpack to handle SCSS and CSS files.
         * Note that loaders are run in reverse order, so here `sass-loader` runs
         * first. This is because the `use` array gets turned into a require call
         * where loaders are applied right-to-left, i.e.:
         * `require("mini-css-extract-loader!css-loader!sass-loader!./filename.css")`
         */
        use: [
          /**
           * ## MiniCssExtractPlugin.loader
           * Pulls CSS out from the JS bundles into their own .css files.
           * In development it is common to use `style-loader` here instead
           * as it allows webpack to inject styles from the JavaScript bundle
           * into the page at runtime, facilitating such things as hot reloading.
           * We don't do that here yet though... something to consider for later.
           */
          MiniCssExtractPlugin.loader,
          /**
           * ## css-loader
           * The css-loader interprets @import and url() like import/require()
           * and will resolve them.
           */
          {
            loader: 'css-loader',
            options: {
              sourceMap: true
            }
          },
          /**
           * ## postcss-loader
           * Used to apply postcss style transforms, such as the autoprefixer plugin
           * which applies vendor prefixes automatically to declarations.
           */
          {
            loader: 'postcss-loader',
            options: {
              sourceMap: true
            }
          },
          /**
           * ## sass-loader
           * Allows webpack to compile SASS stylesheets into CSS during the
           * compilation phase, which then get passed off to the next loader,
           * in our case `css-loader`.
           */
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true
            }
          }
        ],
        test: /\.s?css$/
      },
      /**
       * ## file-loader
       * Copies required or otherwise dependended-upon files into the output
       * directory. For example, font files accessed via the `url()` CSS syntax.
       */
      {
        loader: 'file-loader',
        test: /\.(woff2?|ttf|otf|eot|svg)$/,
        options: {
          name: 'files/[name]__[hash:5].[ext]'
        }
      }
    ]
  },

  plugins: [
    // Remove all previous build assets
    new CleanWebpackPlugin([
      output
    ]),
    // As per loader comments above, this extracts CSS chunks into their own .css file
    // instead of keeping them in the JavaScript bundle.
    new MiniCssExtractPlugin({
      filename: '[name].css'
    }),
    // Copy our static image assets to the public path.
    new CopyWebpackPlugin([{
      from: path.resolve(input, 'assets/img/*'),
      to: path.resolve(input, 'public/img'),
      flatten: true
    }]),
    // Compress all image files using defaults of the plugin.
    new ImageminWebpackPlugin()
  ]
};
