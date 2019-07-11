// DEPENDENCIES
const path = require('path');
const glob = require('glob-all');

// PLUGINS
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');

module.exports = {
  plugins: [
    new OptimizeCssAssetsPlugin({
      cssProcessorOptions: { discardComments: { removeAll: true } },
    }),
  ],
};

