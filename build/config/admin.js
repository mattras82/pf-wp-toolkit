const CopyWebpackPlugin = require('copy-webpack-plugin');

module.exports = {
  entry: {
    'admin': ['@babel/polyfill/noConflict', './_src/scripts/admin.js', './_src/styles/admin.scss'],
    'lazy-images': ['@babel/polyfill/noConflict', './_src/scripts/lazy-images.js']
  },
  plugins: [
    new CopyWebpackPlugin([
      { from: './node_modules/jquery/dist/jquery.min.js' },
      { from: './node_modules/jquery-migrate/dist/jquery-migrate.min.js' },
    ])
  ]
};
