module.exports = (env, argv) => {
  const MiniCSSExtractPlugin = require('mini-css-extract-plugin');
  const LastCallPlugin = require(`last-call-webpack-plugin`);
  if (env.build === 'theme') {
    return {
      entry: {
        'theme': './_src/scripts/lazy-media.js'
      },
      output: {
        // Rename output
        filename: 'lazy-media.js'
      },
      plugins: [
        // Extract CSS into renamed CSS file
        new MiniCSSExtractPlugin({
          filename: 'lazy-media.css'
        }),
        // Remove the theme.css file from output
        new LastCallPlugin({
          assetProcessors: [{
            regExp: /theme/,
            processor: (assetName, asset, assets) => {
              assets.setAsset('theme.css', null);
              return Promise.resolve();
            }
          }]
        })
      ]
    }
  }
};
