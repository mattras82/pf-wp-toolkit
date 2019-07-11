// PLUGINS
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

function issuer(m) {
  return (m.issuer ? issuer(m.issuer) : (m.name ? m.name : false));
}


module.exports = {
  module: {
    rules: [
      {
        test: /\.s?css$/,
        use: [
          MiniCssExtractPlugin.loader,
          { loader: 'css-loader', options: { url: false, sourceMap: true} },
          { loader: 'postcss-loader', options: { sourceMap: true } },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true,
            }
          },
        ]
      }
    ]
  },

  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].css'
    })
  ],

  // Split CSS into separate files based on entry point.
  optimization: {
    splitChunks: {
      cacheGroups: {
        adminCss: {
          name: 'admin',
          test: (m,c,entry = 'admin') => m.constructor.name === 'CssModule' && issuer(m) === entry,
          chunks: 'all',
          enforce: true,
        }
      }
    }
  },
};
