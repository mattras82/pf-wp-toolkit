//@ts-check
'use strict';

const c = require('./logs');
const fs = require('fs');

let helpers = {};

/**
 * Renders a SASS Map as a string
 * @param {String|Object} value
 * @returns {String}
 */
helpers.sassifyValue = value => {
  // map
  if (typeof value === 'object' && value !== null) {
    return `(${Object.keys(value).reduce((_val, _var) => _val += `"${_var}":${helpers.sassifyValue(value[_var])},`, '')})`;
  }
  // string or number
  return value;
};

/**
 * Reduces the sass object in the project's config to a string
 * that can be read by the sass-loader.
 * @param {Object} config
 * @returns {String}
 */
helpers.configSassVariables = config => {
  let sass = config.styles.sass;

  if (!(typeof sass === 'object' && sass !== null)) return '';

  return Object.keys(sass).reduce((variables, variable) => {
    variables += `$${variable}:${helpers.sassifyValue(sass[variable])};`;
    return variables;
  }, '');
}

/**
 * Gets the current project's package.json file
 * @returns {Object}
 */
helpers.getPackage = () => {
  return helpers.getJson(`${process.cwd()}/package.json`);
};


/**
 * Writes to the project's package.json file
 * @param pkg {Object} Package object to save
 * @param async {boolean} Whether or not run asynchronously. Default: false
 * @param cb {function} Callback function called if async is true
 * @returns {boolean}
 */
helpers.setPackage = (pkg, async = false, cb = null) => {
  return helpers.writeJson(`${process.cwd()}/package.json`, pkg, async, cb);
};

/**
 * Writes a JSON object to the given path
 * @param {string} dest Full path to the file
 * @param {Object} obj Object to write
 * @param {boolean} async Whether or not run asynchronously. Default: false
 * @param {function} cb Callback function called if async is true
 * @returns {boolean}
 */
helpers.writeJson = (dest, obj, async = false, cb = undefined) => {
  if (!dest || !obj) {
    throw new Error('Destination & Object cannot be null');
  }
  if (async) {
    // @ts-ignore
    fs.writeFile(dest, JSON.stringify(obj, null, 2), cb);
    return true;
  } else {
    try {
      fs.writeFileSync(dest, JSON.stringify(obj, null, 2));
      return true;
    } catch (e) {
      c.error(`ERROR: Could not write to ${dest}`);
      console.error(e);
      process.exit(1);
    }
  }
  return false;
};

/**
 * Gets a JSON file from the given path
 * @param {string} dest
 * @param {boolean} logError
 * @returns {Object}
 */
helpers.getJson = (dest, logError = true) => {
  if (!dest) {
    throw new Error('Destination cannot be null');
  }
  try {
    try {
      delete require.cache[require.resolve(dest)];
    } catch {
      c.log(`Error in cache module cache resolution of ${dest} file`);
    }
    return require(dest);
  } catch(e) {
    if (logError) {
      c.error(`ERROR: Could not read ${dest}`);
    }
  }
  return {};
};

/**
 * Gets project-defined build options with defaults.
 * @param {Object} defaults
 * @returns {Object}
 */
helpers.getBuildOptions = (defaults = {}) => {
  if (fs.existsSync(`${process.cwd()}/build/options.json`)) {
    return Object.assign(helpers.mergeObjects(defaults, helpers.getJson(`${process.cwd()}/build/options.json`)), {});
  }
  return defaults;
};

/**
 * Recursively deep merges 2 objects;
 * @param {Object} old
 * @param {Object} obj
 * @returns {Object}
 */
helpers.mergeObjects = (old, obj) => {
  let newObj = Object.assign({}, old, obj);
  for (let [key, val] of Object.entries(newObj)) {
    if (typeof val === 'object' && !Array.isArray(val)) {
      if (Object.prototype.hasOwnProperty.call(obj, key) && Object.prototype.hasOwnProperty.call(old, key)) {
        newObj[key] = helpers.mergeObjects(old[key], obj[key]);
      }
    }
  }
  return newObj;
};

/**
 * Saves build options in the project directory
 * @param opts
 * @returns {boolean}
 */
helpers.setBuildOptions = opts => {
  const path = require('path');
  let buildPath = path.resolve(process.cwd(), 'build');
  if (!fs.existsSync(buildPath)) {
    fs.mkdirSync(buildPath)
  }
  return helpers.writeJson(path.resolve(buildPath, 'options.json'), helpers.getBuildOptions(opts));
};

/**
 * List of valid platform names
 * @type {string[]}
 */
helpers.validPlatforms = ['wordpress'];

module.exports = helpers;
