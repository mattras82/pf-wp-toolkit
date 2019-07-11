// HELPERS
const path = require('path');
const merge = require('webpack-merge');

// CONFIG OBJECTS
const admin = require('./build/config/admin');

// UTIL CONFIG OPTIONS
const production = require('./build/util/production');
const scripts = require('./build/util/scripts');
const styles = require('./build/util/styles');

module.exports = (env, argv) => {
  const build = [];

  const base = {
    node: {
      __dirname: true,
      __filename: true
    },
    output: {
      path: path.resolve(__dirname, 'assets'),
      filename: '[name].js',
    },
  };

  build.push(base);
  build.push(scripts);
  build.push(styles);
  build.push(admin);

  if (argv.mode === 'production') {
    build.push(production);
  }

  return merge(...build);
};
