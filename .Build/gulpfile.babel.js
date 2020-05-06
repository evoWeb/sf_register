'use strict';

// Common
import {src, dest, series} from 'gulp';

// JS
import browserify from 'browserify';
import source from 'vinyl-source-stream';
import buffer from 'vinyl-buffer';
import tsify from 'tsify';
import terser from 'gulp-terser';
import rename from 'gulp-rename';

// CSS
import sourcemaps from 'gulp-sourcemaps';
import sass from 'gulp-sass';
import cssnano from 'gulp-cssnano';
import postcss from 'gulp-postcss';
import autoprefixer from 'autoprefixer';

const paths = {
  src: './Sources',
  dest: '../Resources/Public'
};

const tasks = {
  typescript: {
    src: `${paths.src}/TypeScript/sf_register.ts`,
    dest: `${paths.dest}/JavaScript/`
  },
  scss: {
    src: `${paths.src}/Scss/*.scss`,
    dest: `${paths.dest}/Stylesheets/`
  },
  cssnano: {
    src: [
      `${paths.dest}/Stylesheets/*.css`,
      `!${paths.dest}/Stylesheets/*.min.css`
    ],
    rename: {extname: '.min.css'},
    dest: `${paths.dest}/Stylesheets/`
  },
};

let typescriptTask = () => {
  let b = browserify({
    entries: [tasks.typescript.src],
    debug: true
  });

  return b
    .plugin(tsify)
    .bundle()
    .pipe(source('sf_register.js'))
    .pipe(buffer())

    // This will output the non-minified version
    .pipe(dest(tasks.typescript.dest))

    .pipe(sourcemaps.init({loadMaps: true}))
    // Add transformation tasks to the pipeline here.
    .pipe(terser())
    .on('error', console.log)
    .pipe(rename({extname: '.min.js'}))
    .pipe(sourcemaps.write('./', {
      mapFile: function (mapFilePath) {
        // source map files are named *.map instead of *.js.map
        return mapFilePath.replace('.min.js.map', '.js.map');
      }
    }))

    .pipe(dest(tasks.typescript.dest));
};

let stylesTask = () => {
  return src(tasks.scss.src)

    .pipe(sourcemaps.init())
    .pipe(sass().on('error', console.log))
    .pipe(postcss([autoprefixer()]))
    .pipe(sourcemaps.write('./'))

    .pipe(dest(tasks.scss.dest));
};

let cssnanoTask = () => {
  return src(tasks.cssnano.src)
    .pipe(cssnano())
    .pipe(rename(tasks.cssnano.rename))
    .pipe(dest(tasks.cssnano.dest));
};

exports.typescript = typescriptTask;

exports.scss = series(stylesTask, cssnanoTask);

exports.build = series(typescriptTask, stylesTask, cssnanoTask);
