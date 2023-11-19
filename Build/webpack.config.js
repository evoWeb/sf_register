const fs = require('fs');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');

class RemovePlugin {
  static name = 'Remove *.LICENSE.txt';

  apply(compiler) {
    compiler.hooks.done.tap(
      RemovePlugin.name,
      this.done
    );
  }

  /**
   * @param done Stats
   */
  done(done) {
    Object.keys(done.compilation.assets).forEach(file => {
      if (file.indexOf('LICENSE.txt') > 0) {
        fs.unlinkSync(done.compilation.outputOptions.path + '/' + file);
      }
    });
  }
}

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
      {
        test: /\.(sass|scss)$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'postcss-loader',
          'sass-loader'
        ]
      }
    ]
  },

  plugins: [
    new RemovePlugin(),
    new MiniCssExtractPlugin({
      filename: '..' + path.resolve(__dirname, '../Resources/Public/Stylesheets/[name].min.css')
    }),
  ]
};

module.exports = WebpackDefault;
