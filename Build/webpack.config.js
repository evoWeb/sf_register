const WebpackDefault = {
  // bundling mode
  mode: 'development',

  devtool: 'source-map',

  // file resolutions
  resolve: {
    extensions: ['.ts', '.js'],
  },

  // loaders
  module: {
    rules: [
      {
        test: /\.(ts|tsx)$/,
        use: 'ts-loader',
        exclude: /node_modules/
      },
    ]
  }
};

module.exports = WebpackDefault;
