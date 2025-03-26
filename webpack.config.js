// module.exports = (env, argv) => {
//   const MiniCSSExtractPlugin = require('mini-css-extract-plugin');
//   const LastCallPlugin = require(`last-call-webpack-plugin`);
//   if (env.build === 'theme') {
//     return {
//       entry: {
//         'theme': './_src/scripts/lazy-media.js'
//       },
//       output: {
//         // Rename output
//         filename: 'lazy-media.js'
//       },
//       plugins: [
//         // Extract CSS into renamed CSS file
//         new MiniCSSExtractPlugin({
//           filename: 'lazy-media.css'
//         }),
//         // Remove the theme.css file from output
//         new LastCallPlugin({
//           assetProcessors: [{
//             regExp: /theme/,
//             processor: (assetName, asset, assets) => {
//               assets.setAsset('theme.css', null);
//               return Promise.resolve();
//             }
//           }]
//         })
//       ]
//     }
//   }
// };
const production = process.env.NODE_ENV === "production";

// PLUGINS
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const { merge } = require("webpack-merge");

module.exports = (env, argv) => {
  const build = [];

  const base = {
    mode: "development",
  };

  build.push(base);

  const path = require("path");
  build.push({
    entry: {
      admin: ["./_src/scripts/admin.js"],
    },
    output: {
      path: path.resolve(__dirname, "./assets"),
      filename: "[name].js",
      publicPath: "auto",
    },
    mode: production ? "production" : "development",
    externals: {
      jquery: "jQuery",
    },
    module: {
      rules: [
        {
          test: /\.(js|jsx)$/,
          exclude: [
            /node_modules\/(css-loader|core-js|promise-polyfill|webpack|html-webpack-plugin|whatwg-fetch)\//,
          ],
          loader: "babel-loader",
        },
        {
          test: /.(sa|sc|c)ss$/,
          use: [
            {
              loader: MiniCssExtractPlugin.loader,
            },
            {
              // translates CSS into CommonJS modules
              loader: "css-loader",
              options: {
                url: false,
                sourceMap: !production,
              },
            },
            {
              // Run postcss actions
              loader: "postcss-loader",
              options: {
                sourceMap: !production,
              },
            },
            {
              // compiles Sass to CSS
              loader: "sass-loader",
              options: {
                sourceMap: !production,
              },
            },
          ],
        },
      ],
    },

    plugins: [
      new MiniCssExtractPlugin({
        filename: "[name].css",
      }),
    ],

    // Source maps for dev mode
    devtool: production ? false : "inline-cheap-module-source-map",
  });

  if (production) {
    build.push({
      plugins: [
        new CopyWebpackPlugin({
          patterns: [
            { from: `./node_modules/jquery/dist/jquery.min.js` },
            { from: `./node_modules/jquery-migrate/dist/jquery-migrate.min.js` }
          ],
        }),
      ],
    });
  }

  // Merge the build array to one object
  const merged = merge(...build);

  return merged;
};
