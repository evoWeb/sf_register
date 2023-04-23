const path = require('path');
const baseConfig = require('./webpack.config');

const outPath = '/tmp';
const entry = {
  sf_register: path.resolve(__dirname, './Sources/Scss/sf_register.scss'),
};

module.exports = (env, argv) => {
  return {
    ...baseConfig,
    entry: entry,
    mode: argv.mode,
    output: {
      path: outPath,
      filename: '[name].pack.js'
    }
  };
};
