import terser from '@rollup/plugin-terser';
const compress = process.env.COMPRESS === 'true';

export default {
  input: './Public/sf_register.js',
  output: {
    compact: compress,
    file: '../Resources/Public/JavaScript/sf_register' + (compress ? '.min' : '') + '.js',
    sourcemap: true,
    sourcemapFile: '../Resources/Public/JavaScript/sf_register' + (compress ? '.min' : '') + '.js.map',
  },
  plugins: compress ? [] : [
    terser({
      sourceMap: true,
    })
  ]
}
