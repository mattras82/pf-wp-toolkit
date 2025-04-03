'use strict';

const helpers = require('../util/helpers');
const path = require('path');
const configPath = path.resolve(process.cwd(), './config/config.json');

class VersionPlugin {

  run(env) {
    const config = helpers.getJson(configPath);

    if (env === 'production' || (env === 'development' && config.env.production)) {
      let compiled = Object.assign({}, config, {
        env: {
          development: env !== 'production',
          production: env === 'production'
        }
      });

      if (env === 'production') {
        const timestamp = Date.now();

        if (timestamp) {
          compiled.build = timestamp.toString(36);
        }
      }

      helpers.writeJson(configPath, compiled);
    }
  }

  apply(compiler) {
    compiler.hooks.afterEmit.tap('VersionPlugin', () => {
      this.run(compiler.options.mode);
    });
  }
};

module.exports = VersionPlugin;
